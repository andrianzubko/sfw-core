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
    protected static array|false $cache = false;

    /**
     * Scanned sources files.
     */
    protected static array $sources;

    /**
     * Recombines if needed and returns merged paths.
     *
     * @throws Exception\Logic
     * @throws Exception\Runtime
     */
    public static function process(): array
    {
        self::$cache = @include self::$sys['config']['merger_cache'];

        if (self::$cache !== false
            && self::$sys['config']['env'] === 'prod'
            && self::$sys['config']['debug'] === self::$cache['debug']
        ) {
            return static::getPaths();
        }

        if (!self::sys('Locker')->lock('merger')) {
            return static::getPaths();
        }

        if (static::isOutdated()) {
            static::recombine();
        }

        self::sys('Locker')->unlock('merger');

        return static::getPaths();
    }

    /**
     * Gets merged paths.
     */
    protected static function getPaths(): array
    {
        $paths = [];

        $time = self::$cache ? self::$cache['time'] : 0;

        $location = self::$sys['config']['merger_location'];

        foreach (self::$sys['config']['merger_sources'] as $target => $sources) {
            $paths[$target] = "$location/$time.$target";
        }

        return $paths;
    }

    /**
     * Gets sources files.
     */
    protected static function getSources(): array
    {
        if (!isset(self::$sources)) {
            self::$sources = [];

            foreach (self::$sys['config']['merger_sources'] as $target => $sources) {
                foreach ((array) $sources as $source) {
                    if (preg_match('/\.(css|js)$/', $source, $M)) {
                        self::$sources[$M[1]][$target] ??= [];

                        foreach (glob($source) as $file) {
                            if (is_file($file)) {
                                self::$sources[$M[1]][$target][] = $file;
                            }
                        }
                    }
                }
            }
        }

        return self::$sources;
    }

    /**
     * Rechecks of the needs for recombination.
     */
    protected static function isOutdated(): bool
    {
        if (self::$cache === false || self::$sys['config']['debug'] !== self::$cache['debug']) {
            return true;
        }

        $targets = [];

        foreach (self::sys('Dir')->scan(self::$sys['config']['merger_dir'], false, true) as $item) {
            if (is_file($item)
                && preg_match('~/(\d+)\.(.+)$~', $item, $M)
                    && (int) $M[1] === self::$cache['time']
            ) {
                $targets[] = $M[2];
            } else {
                return true;
            }
        }

        $sources = static::getSources();

        if (array_diff(array_keys(array_merge(...array_values($sources))), $targets)) {
            return true;
        }

        foreach (array_keys($sources) as $type) {
            foreach ($sources[$type] as $files) {
                foreach ($files as $file) {
                    if ((int) filemtime($file) > self::$cache['time']) {
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
     * @throws Exception\Logic
     * @throws Exception\Runtime
     */
    protected static function recombine(): void
    {
        self::sys('Dir')->clear(self::$sys['config']['merger_dir']);

        self::$cache = [
            'time' => time(),
            'debug' => self::$sys['config']['debug'],
        ];

        $sources = static::getSources();

        foreach (array_keys($sources) as $type) {
            foreach ($sources[$type] as $target => $files) {
                $file = sprintf(
                    '%s/%s.%s',
                        self::$sys['config']['merger_dir'],
                        self::$cache['time'],
                        $target
                );

                if ($type === 'js') {
                    $contents = static::mergeJs($files);
                } else {
                    $contents = static::mergeCss($files);
                }

                if (!self::sys('File')->put($file, $contents)) {
                    throw new Exception\Runtime("Unable to write file $file");
                }
            }
        }

        if (!self::sys('File')->putVar(self::$sys['config']['merger_cache'], self::$cache)) {
            throw new Exception\Runtime(
                sprintf(
                    'Unable to write file %s',
                        self::$sys['config']['merger_cache']
                )
            );
        }
    }

    /**
     * Merges JS.
     *
     * @throws Exception\Logic
     * @throws Exception\Runtime
     */
    protected static function mergeJs(array $files): string
    {
        $merged = static::mergeFiles($files);

        if (!self::$sys['config']['debug']) {
            try {
                $merged = (new JSMin($merged))->min();
            } catch (\Exception $e) {
                throw (new Exception\Logic($e->getMessage()))
                    ->setFile($e->getFile())
                    ->setLine($e->getLine());
            }
        }

        return $merged;
    }

    /**
     * Merges CSS.
     *
     * @throws Exception\Runtime
     */
    protected static function mergeCss(array $files): string
    {
        $merged = static::mergeFiles($files);

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
     * @throws Exception\Runtime
     */
    protected static function mergeFiles(array $files): string
    {
        $merged = [];

        foreach ($files as $file) {
            $contents = self::sys('File')->get($file);

            if ($contents === false) {
                throw new Exception\Runtime("Unable to read file $file");
            }

            $merged[] = $contents;
        }

        return implode("\n", $merged);
    }
}
