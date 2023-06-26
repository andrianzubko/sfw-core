<?php

namespace SFW\Lazy\Sys;

/**
 * JS and CSS merger.
 */
class Merger extends \SFW\Lazy\Sys
{
    /**
     * Just in case.
     */
    public function __construct() {}

    /**
     * Recombine all if needed and return merged info.
     */
    public function get(bool $recombine = true): array
    {
        if ($recombine) {
            $this->recombine();
        }

        foreach (@self::$sys->dir()->scan('public/.merged') as $item) {
            return ['time' => (int) $item];
        }

        return ['time' => false];
    }

    /**
     * Preparing struct of files to merging.
     */
    protected function prepareStruct(): array
    {
        $struct = [];

        foreach (['css','js'] as $type) {
            foreach (['primary','secondary'] as $section) {
                $dir = "public/.$type/$section";

                foreach (@self::$sys->dir()->scan($dir, true) as $item) {
                    $file = "$dir/$item";

                    if (!is_file($file) || !str_ends_with($item, ".$type")) {
                        continue;
                    }

                    if (preg_match('~^([^/]+)/~', $item, $M)) {
                        $struct[$type]["public/.merged/%s.{$M[1]}.$type"][] = $file;

                        $struct[$type]["public/.merged/%s.{$M[1]}.$section.$type"][] = $file;
                    } else {
                        $struct[$type]["public/.merged/%s.$type"][] = $file;

                        $struct[$type]["public/.merged/%s.$section.$type"][] = $file;
                    }
                }
            }
        }

        return $struct;
    }

    /**
     * Recombining all.
     */
    protected function recombine(): void
    {
        // {{{ locking

        if (self::$sys->locker()->lock('merger') === false) {
            return;
        }

        // }}}
        // {{{ preparing struct of files

        $struct = $this->prepareStruct();

        if (!$struct) {
            self::$sys->dir()->clear('public/.merged');

            return;
        }

        // }}}
        // {{{ looking for time-prefix of merged files and some checking consistency

        $count = 0;

        $time = false;

        foreach (@self::$sys->dir()->scan('public/.merged') as $item) {
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
                foreach ($struct[$type] as $pattern => $files) {
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
            self::$sys->dir()->clear('public/.merged');

            $time = time();

            foreach (array_keys($struct) as $type) {
                foreach ($struct[$type] as $pattern => $files) {
                    $merged = $this->{$type}($files);

                    if (self::$sys->file()->put(sprintf($pattern, $time), $merged) === false) {
                        self::$sys->abend()->error();
                    }
                }
            }
        }

        // }}}
        // {{{ unlocking

        self::$sys->locker()->unlock('merger');

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
            self::$sys->abend()->error($error->getMessage());
        }

        return $merged;
    }

    /**
     * Merging CSS.
     */
    protected function css(array $files): string
    {
        $merged = $this->files($files);

        $merged = preg_replace_callback('~/\*(.*?)\*/~us', fn($M) => strlen($M[1]) ? '' : '/**/', $merged);

        $merged = self::$sys->text()->fulltrim($merged);

        $merged = preg_replace('/([ }]*})\s*/u', "\$1\n", $merged);

        $merged = preg_replace_callback('/url\(\s*([^\)]+)\s*\)/u',
            function (array $M) use ($merged): string {
                $data = $type = false;

                if (count(explode($M[1], $merged, 3)) == 2
                    && preg_match('~^/.+\.(gif|png|jpg|jpeg|svg|woff|woff2)\b$~ui', $M[1], $N)
                ) {
                    $type = strtolower($N[1]);

                    if ($type === 'jpg') {
                        $type = 'jpeg';
                    } elseif ($type === 'svg') {
                        $type = 'svg+xml';
                    }

                    $size = @filesize('public' . $M[1]);

                    if ($size !== false && $size <= 32 * 1024) {
                        $data = @self::$sys->file()->get('public' . $M[1]);
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
            $content = self::$sys->file()->get($file);

            if ($content === false) {
                self::$sys->abend()->error();
            }

            $merged[] = $content;
        }

        return implode("\n", $merged);
    }
}
