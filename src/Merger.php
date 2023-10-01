<?php

namespace SFW;

use JSMin\JSMin;

/**
 * JS and CSS merger.
 */
class Merger extends Base
{
    /**
     * Internal cache.
     */
    protected array|false $cache = false;

    /**
     * Scanned sources files.
     */
    protected array $sources;

    /**
     * Recombines if needed and returns merged paths.
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    public function process(): array
    {
        $this->cache = @include self::$config['sys']['merger']['cache'];

        if ($this->cache !== false
            && self::$config['sys']['env'] === 'prod'
                && self::$config['sys']['debug'] === $this->cache['debug']
        ) {
            return $this->getPaths();
        }

        if (!$this->sys('Locker')->lock('merger')) {
            return $this->getPaths();
        }

        if ($this->isOutdated()) {
            $this->recombine();
        }

        $this->sys('Locker')->unlock('merger');

        return $this->getPaths();
    }

    /**
     * Gets merged paths.
     */
    protected function getPaths(): array
    {
        $paths = [];

        $time = $this->cache ? $this->cache['time'] : 0;

        foreach (self::$config['sys']['merger']['sources'] as $targets) {
            foreach ((array) $targets as $target) {
                $paths[$target] = sprintf(
                    '%s/%s.%s',
                        self::$config['sys']['merger']['location'],
                        $time,
                        $target
                );
            }
        }

        return $paths;
    }

    /**
     * Gets sources files.
     */
    protected function getSources(): array
    {
        if (!isset($this->sources)) {
            $this->sources = [];

            foreach (self::$config['sys']['merger']['sources'] as $source => $targets) {
                if (preg_match('/\.(css|js)$/', $source, $M)) {
                    foreach ((array) $targets as $target) {
                        $this->sources[$M[1]][$target] ??= [];

                        foreach (glob($source) as $item) {
                            if (is_file($item)) {
                                $this->sources[$M[1]][$target][] = $item;
                            }
                        }
                    }
                }
            }
        }

        return $this->sources;
    }

    /**
     * Rechecks of the needs for recombination.
     */
    protected function isOutdated(): bool
    {
        if ($this->cache === false
            || self::$config['sys']['debug'] !== $this->cache['debug']
        ) {
            return true;
        }

        $targets = [];

        foreach ($this->sys('Dir')->scan(self::$config['sys']['merger']['dir'], false, true) as $item) {
            if (is_file($item)
                && preg_match('~/(\d+)\.(.+)$~', $item, $M)
                    && (int) $M[1] === $this->cache['time']
            ) {
                $targets[] = $M[2];
            } else {
                return true;
            }
        }

        $sources = $this->getSources();

        if (array_diff(
                array_keys(
                    array_merge(
                        ...array_values($sources)
                    )
                ), $targets
            )
        ) {
            return true;
        }

        foreach (array_keys($sources) as $type) {
            foreach ($sources[$type] as $files) {
                foreach ($files as $file) {
                    if ((int) filemtime($file) > $this->cache['time']) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Recombines all.
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    protected function recombine(): void
    {
        $this->sys('Dir')->clear(self::$config['sys']['merger']['dir']);

        $this->cache = [
            'time' => time(),
            'debug' => self::$config['sys']['debug'],
        ];

        $sources = $this->getSources();

        foreach (array_keys($sources) as $type) {
            foreach ($sources[$type] as $target => $files) {
                $file = sprintf(
                    '%s/%s.%s',
                        self::$config['sys']['merger']['dir'],
                        $this->cache['time'],
                        $target
                );

                if ($type === 'js') {
                    $contents = $this->mergeJs($files);
                } else {
                    $contents = $this->mergeCss($files);
                }

                if (!$this->sys('File')->put($file, $contents)) {
                    throw new RuntimeException("Unable to write file $file");
                }
            }
        }

        if (!$this->sys('File')->putVar(
                self::$config['sys']['merger']['cache'], $this->cache)
        ) {
            throw new RuntimeException(
                sprintf(
                    'Unable to write file %s',
                        self::$config['sys']['merger']['cache']
                )
            );
        }
    }

    /**
     * Merges JS.
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    protected function mergeJs(array $files): string
    {
        $merged = $this->mergeFiles($files);

        if (!self::$config['sys']['debug']) {
            try {
                $merged = (new JSMin($merged))->min();
            } catch (\Exception $e) {
                throw (new LogicException($e->getMessage()))
                    ->setFile($e->getFile())
                    ->setLine($e->getLine());
            }
        }

        return $merged;
    }

    /**
     * Merges CSS.
     *
     * @throws RuntimeException
     */
    protected function mergeCss(array $files): string
    {
        $merged = $this->mergeFiles($files);

        if (!self::$config['sys']['debug']) {
            $merged = $this->sys('Text')->fTrim(
                preg_replace('~/\*(.*?)\*/~us', '', $merged)
            );
        }

        return preg_replace_callback('/url\(\s*(.+?)\s*\)/u',
            function (array $M) {
                $data = $type = false;

                if (preg_match('/\.(gif|png|jpg|jpeg|svg|woff|woff2)$/ui', $M[1], $N)
                    && str_starts_with($M[1], '/')
                        && !str_starts_with($M[1], '//')
                            && !str_contains($M[1], '..')
                ) {
                    $type = strtolower($N[1]);

                    if ($type === 'jpg') {
                        $type = 'jpeg';
                    } elseif ($type === 'svg') {
                        $type = 'svg+xml';
                    }

                    $size = @filesize(APP_DIR . '/public/' . $M[1]);

                    if ($size !== false
                        && $size <= 32 * 1024
                    ) {
                        $data = $this->sys('File')->get(APP_DIR . '/public/' . $M[1]);
                    }
                }

                if ($data !== false) {
                    return sprintf('url(data:image/%s;base64,%s)', $type, base64_encode($data));
                } else {
                    return sprintf('url(%s)', $M[1]);
                }
            }, $merged
        );
    }

    /**
     * Merges files.
     *
     * @throws RuntimeException
     */
    protected function mergeFiles(array $files): string
    {
        $merged = [];

        foreach ($files as $file) {
            $contents = $this->sys('File')->get($file);

            if ($contents === false) {
                throw new RuntimeException("Unable to read file $file");
            }

            $merged[] = $contents;
        }

        return implode("\n", $merged);
    }
}
