<?php

namespace SFW;

use JSMin\JSMin;
use SFW\Exception\{Logic, Runtime};

/**
 * JS and CSS merger.
 */
final class Merger extends Base
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
     * Merging if needed and returns merged paths.
     *
     * @throws Logic
     * @throws Runtime
     */
    public function merge(): array
    {
        $this->cache = @include self::$sys['config']['merger_cache'];

        if ($this->cache !== false
            && self::$sys['config']['env'] === 'prod'
            && self::$sys['config']['debug'] === $this->cache['debug']
        ) {
            return $this->getPaths();
        }

        if (!self::sys('Locker')->lock('merger')) {
            return $this->getPaths();
        }

        if ($this->isOutdated()) {
            $this->recombine();
        }

        self::sys('Locker')->unlock('merger');

        return $this->getPaths();
    }

    /**
     * Gets merged paths.
     */
    protected function getPaths(): array
    {
        $paths = [];

        $time = $this->cache ? $this->cache['time'] : 0;

        $location = self::$sys['config']['merger_location'];

        foreach (self::$sys['config']['merger_sources'] as $target => $sources) {
            $paths[$target] = "$location/$time.$target";
        }

        return $paths;
    }

    /**
     * Gets sources files.
     */
    protected function scanForSources(): void
    {
        if (!isset($this->sources)) {
            $this->sources = [];

            foreach (self::$sys['config']['merger_sources'] as $target => $sources) {
                foreach ((array) $sources as $source) {
                    if (preg_match('/\.(css|js)$/', $source, $M)) {
                        $this->sources[$M[1]][$target] ??= [];

                        foreach (glob($source) as $file) {
                            if (is_file($file)) {
                                $this->sources[$M[1]][$target][] = $file;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Rechecks of the needs for recombination.
     */
    protected function isOutdated(): bool
    {
        if ($this->cache === false
            || self::$sys['config']['debug'] !== $this->cache['debug']
        ) {
            return true;
        }

        $targets = [];

        foreach (self::sys('Dir')->scan(self::$sys['config']['merger_dir'], false, true) as $item) {
            if (is_file($item)
                && preg_match('~/(\d+)\.(.+)$~', $item, $M)
                && (int) $M[1] === $this->cache['time']
            ) {
                $targets[] = $M[2];
            } else {
                return true;
            }
        }

        $this->scanForSources();

        if (array_diff(array_keys(array_merge(...array_values($this->sources))), $targets)) {
            return true;
        }

        foreach (array_keys($this->sources) as $type) {
            foreach ($this->sources[$type] as $files) {
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
     * @throws Logic
     * @throws Runtime
     */
    protected function recombine(): void
    {
        self::sys('Dir')->clear(self::$sys['config']['merger_dir']);

        $this->cache = [];

        $this->cache['time'] = time();

        $this->cache['debug'] = self::$sys['config']['debug'];

        $this->scanForSources();

        foreach (array_keys($this->sources) as $type) {
            foreach ($this->sources[$type] as $target => $files) {
                $file = sprintf('%s/%s.%s',
                    self::$sys['config']['merger_dir'],
                    $this->cache['time'],
                    $target
                );

                if ($type === 'js') {
                    $contents = $this->mergeJs($files);
                } else {
                    $contents = $this->mergeCss($files);
                }

                if (!self::sys('File')->put($file, $contents)) {
                    throw new Runtime("Unable to write file $file");
                }
            }
        }

        if (!self::sys('File')->putVar(self::$sys['config']['merger_cache'], $this->cache)) {
            throw new Runtime(
                sprintf('Unable to write file %s', self::$sys['config']['merger_cache'])
            );
        }
    }

    /**
     * Merges JS.
     *
     * @throws Logic
     * @throws Runtime
     */
    protected function mergeJs(array $files): string
    {
        $merged = $this->mergeFiles($files);

        if (!self::$sys['config']['debug']) {
            try {
                $merged = (new JSMin($merged))->min();
            } catch (\Exception $e) {
                throw (new Logic($e->getMessage()))->setFile($e->getFile())->setLine($e->getLine());
            }
        }

        return $merged;
    }

    /**
     * Merges CSS.
     *
     * @throws Runtime
     */
    protected function mergeCss(array $files): string
    {
        $merged = $this->mergeFiles($files);

        if (!self::$sys['config']['debug']) {
            $merged = self::sys('Text')->fTrim(preg_replace('~/\*(.*?)\*/~us', '', $merged));
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

                    if ($size !== false && $size <= 32 * 1024) {
                        $data = self::sys('File')->get(APP_DIR . '/public/' . $M[1]);
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
     * @throws Runtime
     */
    protected function mergeFiles(array $files): string
    {
        $merged = [];

        foreach ($files as $file) {
            $contents = self::sys('File')->get($file);

            if ($contents === false) {
                throw new Runtime("Unable to read file $file");
            }

            $merged[] = $contents;
        }

        return implode("\n", $merged);
    }
}
