<?php

namespace SFW;

use JSMin\JSMin;

/**
 * JS and CSS merger.
 */
class Merger extends Base
{
    /**
     * Passing parameters to properties.
     */
    public function __construct(protected array $sources)
    {
    }

    /**
     * Recombining if needed and returning merged paths.
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    public function get(): array
    {
        $cache = @include self::$config['sys']['merger']['cache'];

        if ($cache !== false
            && self::$config['sys']['env'] === 'prod'
            && self::$config['sys']['debug'] === $cache['debug']
        ) {
            return $this->getPaths($cache);
        }

        if ($this->sys('Locker')->lock('merger') === false) {
            return $this->getPaths($cache);
        }

        $cache = @include self::$config['sys']['merger']['cache'];

        $sources = $this->getSources();

        $cache = $this->checkVersion($cache, $sources);

        if ($cache === false) {
            $cache = $this->recombine($sources);
        }

        $this->sys('Locker')->unlock('merger');

        return $this->getPaths($cache);
    }

    /**
     * Getting merged paths.
     */
    protected function getPaths(array|false $cache): array
    {
        $time = $cache ? $cache['time'] : 0;

        $paths = [];

        foreach ($this->sources as $targets) {
            foreach ((array) $targets as $target) {
                $paths[$target] = self::$config['sys']['merger']['location'] . "/$time.$target";
            }
        }

        return $paths;
    }

    /**
     * Getting sources files.
     */
    protected function getSources(): array
    {
        $sources = [];

        foreach ($this->sources as $source => $targets) {
            if (preg_match('/\.(css|js)$/', $source, $M)) {
                foreach ((array) $targets as $target) {
                    $sources[$M[1]][$target] ??= [];

                    foreach (glob($source) as $item) {
                        if (is_file($item)) {
                            $sources[$M[1]][$target][] = $item;
                        }
                    }
                }
            }
        }

        return $sources;
    }

    /**
     * Recheck of the needs for recombination.
     */
    protected function checkVersion(array|false $cache, array $sources): array|false
    {
        if ($cache === false) {
            return false;
        }

        if (self::$config['sys']['debug'] !== $cache['debug']) {
            return false;
        }

        $targets = [];

        foreach (@$this->sys('Dir')->scan(self::$config['sys']['merger']['dir']) as $item) {
            if (is_file(self::$config['sys']['merger']['dir'] . "/$item")
                && preg_match('/^(\d+)\.(.+)$/', $item, $M)
                    && (int) $M[1] === $cache['time']
            ) {
                $targets[] = $M[2];
            } else {
                return false;
            }
        }

        if (array_diff(
                array_keys(
                    array_merge(
                        ...array_values($sources)
                    )
                ), $targets
            )
        ) {
            return false;
        }

        foreach (array_keys($sources) as $type) {
            foreach ($sources[$type] as $files) {
                foreach ($files as $file) {
                    if ((int) filemtime($file) > $cache['time']) {
                        return false;
                    }
                }
            }
        }

        return $cache;
    }

    /**
     * Recombining.
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    protected function recombine(array $sources): array
    {
        $this->sys('Dir')->clear(self::$config['sys']['merger']['dir']);

        $cache = [
            'time' => time(),
            'debug' => self::$config['sys']['debug'],
        ];

        foreach (array_keys($sources) as $type) {
            foreach ($sources[$type] as $target => $files) {
                $file = self::$config['sys']['merger']['dir'] . "/{$cache['time']}.$target";

                if ($type === 'js') {
                    $contents = $this->mergeJs($files);
                } else {
                    $contents = $this->mergeCss($files);
                }

                if (!$this->sys('File')->put($file, $contents)) {
                    throw new RuntimeException(
                        sprintf(
                            'Unable to write file %s',
                                $file
                        )
                    );
                }
            }
        }

        if (!$this->sys('File')->putVar(
                self::$config['sys']['merger']['cache'], $cache)
        ) {
            throw new RuntimeException(
                sprintf(
                    'Unable to write file %s',
                        self::$config['sys']['merger']['cache']
                )
            );
        }

        return $cache;
    }

    /**
     * Merging JS.
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    public function mergeJs(array $files): string
    {
        $merged = $this->mergeFiles($files);

        if (!self::$config['sys']['debug']) {
            try {
                $merged = (new JSMin($merged))->min();
            } catch (\Exception $error) {
                throw (new LogicException($error->getMessage()))
                    ->setFile($error->getFile())
                    ->setLine($error->getLine());
            }
        }

        return $merged;
    }

    /**
     * Merging CSS.
     *
     * @throws RuntimeException
     */
    public function mergeCss(array $files): string
    {
        $merged = $this->mergeFiles($files);

        if (!self::$config['sys']['debug']) {
            $merged = preg_replace('~/\*(.*?)\*/~us', '', $merged);

            $merged = $this->sys('Text')->fTrim($merged);
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

                    $size = @filesize(APP_DIR . "/public/$M[1]");

                    if ($size !== false
                        && $size <= 32 * 1024
                    ) {
                        $data = @$this->sys('File')->get(APP_DIR . "/public/$M[1]");
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
     * Merging files.
     *
     * @throws RuntimeException
     */
    public function mergeFiles(array $files): string
    {
        $merged = [];

        foreach ($files as $file) {
            $contents = $this->sys('File')->get($file);

            if ($contents === false) {
                throw new RuntimeException(
                    sprintf(
                        'Unable to read file %s',
                            $file
                    )
                );
            }

            $merged[] = $contents;
        }

        return implode("\n", $merged);
    }
}
