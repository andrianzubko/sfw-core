<?php

namespace SFW;

/**
 * JS and CSS merger.
 */
class Merger extends Base
{
    /**
     * Merged dir.
     */
    protected string $mergedDir = APP_DIR . '/var/cache/merged';

    /**
     * Merged dir version file.
     */
    protected string $versionFile = APP_DIR . '/var/cache/merged.version.php';

    /**
     * Passing parameters to properties.
     */
    public function __construct(protected array $sources) {}

    /**
     * Recombining if needed and returning merged paths.
     */
    public function get(array $options = []): array
    {
        $version = @include $this->versionFile;

        if ($version !== false
            && !($options['recheck'] ?? true)
                && ($options['minify'] ?? true) === $version['minify']
        ) {
            return $this->getPaths($version['time']);
        }

        if ($this->sys('Locker')->lock('merger') === false) {
            return $this->getPaths(
                $version !== false
                    ? $version['time']
                    : 0
            );
        }

        $version = @include $this->versionFile;

        $sources = $this->getSources();

        $version = $this->checkVersion($version, $sources, $options['minify'] ?? true);

        if ($version === false) {
            $version = $this->recombine($sources, $options['minify'] ?? true);
        }

        $this->sys('Locker')->unlock('merger');

        return $this->getPaths($version['time']);
    }

    /**
     * Getting merged paths.
     */
    protected function getPaths(int $time): array
    {
        $paths = [];

        foreach ($this->sources as $targets) {
            foreach ((array) $targets as $target) {
                $paths[$target] = "/.merged/$time.$target";
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
    protected function checkVersion(array|false $version, array $sources, bool $minify): array|false
    {
        if ($version === false) {
            return false;
        }

        if ($minify !== $version['minify']) {
            return false;
        }

        $targets = [];

        foreach (@$this->sys('Dir')->scan($this->mergedDir) as $item) {
            if (is_file("$this->mergedDir/$item")
                && preg_match('/^(\d+)\.(.+)$/', $item, $M)
                    && (int) $M[1] === $version['time']
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
                    if ((int) filemtime($file) > $version['time']) {
                        return false;
                    }
                }
            }
        }

        return $version;
    }

    /**
     * Recombining.
     */
    protected function recombine(array $sources, bool $minify): array
    {
        $this->sys('Dir')->clear($this->mergedDir);

        $version = [
            'time' => time(),
            'minify' => $minify,
        ];

        foreach (array_keys($sources) as $type) {
            foreach ($sources[$type] as $target => $files) {
                $file = "$this->mergedDir/{$version['time']}.$target";

                if ($type === 'js') {
                    $contents = $this->mergeJs($files, $minify);
                } else {
                    $contents = $this->mergeCss($files, $minify);
                }

                if ($this->sys('File')->put($file, $contents) === false) {
                    $this->sys('Abend')->error();
                }
            }
        }

        $this->sys('File')->putVar($this->versionFile, $version);

        return $version;
    }

    /**
     * Merging JS.
     */
    protected function mergeJs(array $files, bool $minify): string
    {
        $merged = $this->mergeFiles($files);

        if ($minify) {
            $jsmin = new \JSMin\JSMin($merged);

            try {
                $merged = $jsmin->min();
            } catch (\Exception $error) {
                $this->sys('Abend')->error($error->getMessage());
            }
        }

        return $merged;
    }

    /**
     * Merging CSS.
     */
    protected function mergeCss(array $files, bool $minify): string
    {
        $merged = $this->mergeFiles($files);

        if ($minify) {
            $merged = $this->sys('Text')->fulltrim(
                preg_replace('~/\*(.*?)\*/~us', '', $merged)
            );
        }

        return preg_replace_callback('/url\(\s*(.+?)\s*\)/u',
            function (array $M): string {
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
     */
    protected function mergeFiles(array $files): string
    {
        $merged = [];

        foreach ($files as $file) {
            $contents = $this->sys('File')->get($file);

            if ($contents === false) {
                $this->sys('Abend')->error();
            }

            $merged[] = $contents;
        }

        return implode("\n", $merged);
    }
}
