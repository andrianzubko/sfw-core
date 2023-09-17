<?php

namespace SFW;

use JSMin\JSMin;

/**
 * JS and CSS merger.
 */
class Merger extends Base
{
    /**
     * File with version of merged JS and CSS files.
     */
    protected string $versionFile;

    /**
     * Target directory for merged JS and CSS files.
     */
    protected string $targetDir;

    /**
     * Directory with merged JS and CSS files relatively to public directory.
     */
    protected string $publicDir;

    /**
     * Passing parameters to properties.
     */
    public function __construct(protected array $sources)
    {
        $this->versionFile = self::$config['sys']['merger']['version_file'];

        $this->targetDir = self::$config['sys']['merger']['target_dir'];

        $this->publicDir = self::$config['sys']['merger']['public_dir'];
    }

    /**
     * Recombining if needed and returning merged paths.
     *
     * @throws LogicException
     * @throws RuntimeException
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
                $paths[$target] = "$this->publicDir/$time.$target";
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

        foreach (@$this->sys('Dir')->scan($this->targetDir) as $item) {
            if (is_file("$this->targetDir/$item")
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
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    protected function recombine(array $sources, bool $minify): array
    {
        $this->sys('Dir')->clear($this->targetDir);

        $version = [
            'time' => time(),
            'minify' => $minify,
        ];

        foreach (array_keys($sources) as $type) {
            foreach ($sources[$type] as $target => $files) {
                $file = "$this->targetDir/{$version['time']}.$target";

                if ($type === 'js') {
                    $contents = $this->mergeJs($files, $minify);
                } else {
                    $contents = $this->mergeCss($files, $minify);
                }

                if ($this->sys('File')->put($file, $contents) === false) {
                    throw new RuntimeException(
                        sprintf(
                            'Unable to write file %s',
                                $file
                        )
                    );
                }
            }
        }

        if ($this->sys('File')->putVar($this->versionFile, $version) === false) {
            throw new RuntimeException(
                sprintf(
                    'Unable to write file %s',
                        $this->versionFile
                )
            );
        }

        return $version;
    }

    /**
     * Merging JS.
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    public function mergeJs(array $files, bool $minify): string
    {
        $merged = $this->mergeFiles($files);

        if ($minify) {
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
    public function mergeCss(array $files, bool $minify): string
    {
        $merged = $this->mergeFiles($files);

        if ($minify) {
            $merged = $this->sys('Text')->fullTrim(
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
