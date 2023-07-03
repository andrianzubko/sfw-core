<?php

namespace SFW;

/**
 * JS and CSS merger.
 */
class Merger extends Base
{
    /**
     * Passing merged dir to properties.
     */
    public function __construct(protected string $mergedDir)
    {
        $this->mergedDir = PUB_DIR . "/$this->mergedDir";
    }

    /**
     * Returns merged time.
     */
    public function get(): int|false
    {
        foreach (@$this->sys('Dir')->scan($this->mergedDir) as $item) {
            return (int) $item;
        }

        return false;
    }

    /**
     * Recombining all.
     */
    public function recombine(array $sources): void
    {
        // {{{ locking

        if ($this->sys('Locker')->lock('merger') === false) {
            return;
        }

        // }}}
        // {{{ preparing struct of files

        $struct = [];

        foreach (array_keys($sources) as $from) {
            if (preg_match('/\.(css|js)$/', $from, $M)) {
                foreach ($sources[$from] as $to) {
                    foreach (glob(PUB_DIR . "/$from") as $file) {
                        if (str_ends_with($file, $M[0])
                            && is_file($file)
                        ) {
                            $struct[$M[1]][$to][] = $file;
                        }
                    }
                }
            }
        }

        if (!$struct) {
            $this->sys('Dir')->clear($this->mergedDir);

            return;
        }

        // }}}
        // {{{ looking for time-prefix of merged files and some checking of consistency

        $count = 0;

        $time = false;

        foreach (@$this->sys('Dir')->scan($this->mergedDir) as $item) {
            if ($time === false) {
                $time = (int) $item;
            } elseif ($time != (int) $item) {
                $time = false;

                break;
            }

            $count += 1;
        }

        if ($time !== false
            && $count != array_sum(array_map(fn($a) => count($a), $struct))
        ) {
            $time = false;
        }

        // }}}
        // {{{ comparing merged and sources times

        if ($time !== false) {
            foreach (array_keys($struct) as $type) {
                foreach ($struct[$type] as $target => $files) {
                    foreach ($files as $file) {
                        if ((int) filemtime($file) > $time) {
                            $time = false;

                            break 3;
                        }
                    }
                }
            }
        }

        // }}}
        // {{{ merging if needed

        if ($time === false) {
            $this->sys('Dir')->clear($this->mergedDir);

            $time = time();

            foreach (array_keys($struct) as $type) {
                foreach ($struct[$type] as $target => $files) {
                    $merged = $this->{$type}($files);

                    if ($this->sys('File')->put("$this->mergedDir/$time.$target", $merged) === false) {
                        $this->sys('Abend')->error();
                    }
                }
            }
        }

        // }}}
        // {{{ unlocking

        $this->sys('Locker')->unlock('merger');

        // }}}
    }

    /**
     * Merging JS.
     */
    protected function js(array $files): string
    {
        $merged = $this->files($files);

        $jsmin = new \JSMin\JSMin($merged);

        try {
            $merged = $jsmin->min();
        } catch (\Exception $error) {
            $this->sys('Abend')->error($error->getMessage());
        }

        return $merged;
    }

    /**
     * Merging CSS.
     */
    protected function css(array $files): string
    {
        $merged = $this->files($files);

        $merged = preg_replace('~/\*(.*?)\*/~us', '', $merged);

        $merged = $this->sys('Text')->fulltrim($merged);

        $merged = preg_replace_callback('/url\( ?(.+?) ?\)/u',
            function (array $M) use ($merged): string {
                $data = $type = false;

                if (preg_match('/\.(gif|png|jpg|jpeg|svg|woff|woff2)$/ui', $M[1], $N)
                    && str_starts_with($M[1], '/')
                        && !str_contains($M[1], '..')
                ) {
                    $type = strtolower($N[1]);

                    if ($type === 'jpg') {
                        $type = 'jpeg';
                    } elseif ($type === 'svg') {
                        $type = 'svg+xml';
                    }

                    $size = @filesize(PUB_DIR . $M[1]);

                    if ($size !== false && $size <= 32 * 1024) {
                        $data = @$this->sys('File')->get(PUB_DIR . $M[1]);
                    }
                }

                if ($data !== false) {
                    return sprintf('url(data:image/%s;base64,%s)', $type, base64_encode($data));
                } else {
                    return sprintf('url(%s)', $M[1]);
                }
            }, $merged
        );

        return $merged;
    }

    /**
     * Merging files.
     */
    protected function files(array $files): string
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
