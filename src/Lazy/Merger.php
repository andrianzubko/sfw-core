<?php

namespace SFW\Lazy;

/**
 * JS and CSS merger.
 */
class Merger extends \App\Lazy
{
    /**
     * Javascript directories.
     */
    protected array $jsDir = [
        'primary.js' => 'public/.js/primary/',

        'secondary.js' => 'public/.js/secondary/',
    ];

    /**
     * CSS directories.
     */
    protected array $cssDir = [
        'primary.css' => 'public/.css/primary/',

        'secondary.css' => 'public/.css/secondary/',
    ];

    /**
     * Directory for merged files.
     */
    protected string $dir = 'public/.merged/';

    /**
     * Get current prefix of merged files.
     */
    public function getPrefix(): string|false
    {
        $time = @filemtime($this->dir);

        return $time === false ? false : (string) $time;
    }

    /**
     * Recombining all.
     */
    public function recombine(): void
    {
        // {{{ locking

        if ($this->locker()->lock('merger') === false) {
            return;
        }

        // }}}
        // {{{ processing

        $sections = [];

        foreach ($this->jsDir as $name => $dir) {
            if (is_dir($dir)) {
                foreach (scandir($dir) as $file) {
                    if (preg_match('/\.js$/', $file)) {
                        $sections['js'][$this->dir . sprintf('%%s.script.%%s')][$name][] = $dir . $file;
                    }
                }
            }
        }

        foreach ($this->cssDir as $name => $dir) {
            if (is_dir($dir)) {
                foreach (scandir($dir) as $file) {
                    if (preg_match('/^([a-z])(?:\..*)?\.css$/', $file, $M)) {
                        $sections['css'][$this->dir . sprintf('%%s.styles.%s.%%s', $M[1])][$name][] = $dir . $file;
                    }
                }
            }
        }

        $mergedTime = @filemtime($this->dir);

        if ($sections) {
            if ($mergedTime !== false) {
                foreach (scandir($this->dir) as $file) {
                    if (preg_match('/^(\d+)\./', $file, $M) && $M[1] != $mergedTime) {
                        $mergedTime = false;

                        break;
                    }
                }
            }

            if ($mergedTime !== false) {
                foreach (array_keys($sections) as $section) {
                    foreach (array_keys($sections[$section]) as $pattern) {
                        foreach ($sections[$section][$pattern] as $name => $files) {
                            foreach ($files as $file) {
                                if ((int) filemtime($file) > $mergedTime) {
                                    $mergedTime = false;

                                    break 4;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $this->dir()->remove($this->dir);

            $mergedTime = false;
        }

        if ($sections && $mergedTime === false) {
            $this->dir()->remove($this->dir);

            if ($this->dir()->create($this->dir) === false) {
                $this->abend()->error();
            }

            $mergedTime = @filemtime($this->dir);

            foreach (array_keys($sections) as $section) {
                foreach (array_keys($sections[$section]) as $pattern) {
                    foreach ($sections[$section][$pattern] as $name => $files) {
                        $sections[$section][$pattern][$name] = $this->{$section}($files);
                    }

                    $sections[$section][$pattern][$section] = implode("\n", $sections[$section][$pattern]);

                    foreach ($sections[$section][$pattern] as $name => $merged) {
                        if ($this->file()->put(sprintf($pattern, $mergedTime, $name), $merged) === false) {
                            $this->abend()->error();
                        }
                    }
                }
            }
        }

        // }}}
        // {{{ unlocking

        $this->locker()->unlock('merger');

        // }}}
    }

    /**
     * Merging Javascript.
     */
    protected function js(array $files): string
    {
        $merged = $this->merge($files);

        $jsmin = new \JSMin\JSMin($merged);

        try {
            $merged = $jsmin->min();
        } catch (\Exception $error) {
            $this->abend()->error($error->getMessage());
        }

        return $merged;
    }

    /**
     * Merging CSS.
     */
    protected function css(array $files): string
    {
        $merged = $this->merge($files);

        $merged = preg_replace_callback('~/\*(.*?)\*/~us', fn($M) => strlen($M[1]) ? '' : '/**/', $merged);

        $merged = $this->text()->fulltrim($merged);

        $merged = preg_replace('/([ }]*})\s*/u', "\$1\n", $merged);

        $merged = preg_replace_callback('/url\(\s*([^\)]+)\s*\)/u',
            function (array $M) use ($merged): string {
                $data = $type = false;

                if (count(explode($M[1], $merged, 3)) == 2) {
                    if (preg_match('~^/.+\.(gif|png|jpg|jpeg|svg|woff|woff2)\b$~ui', $M[1], $N)) {
                        $type = strtolower($N[1]);

                        if ($type === 'jpg') {
                            $type = 'jpeg';
                        } elseif ($type === 'svg') {
                            $type = 'svg+xml';
                        }

                        $size = @filesize('..' . $M[1]);

                        if ($size !== false && $size <= 32 * 1024) {
                            $data = @$this->file()->get('..' . $M[1]);
                        }
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
    protected function merge(array $files): string
    {
        $merged = [];

        foreach ($files as $file) {
            $content = $this->file()->get($file);

            if ($content === false) {
                $this->abend()->error();
            }

            $merged[] = $content;
        }

        return implode("\n", $merged);
    }
}
