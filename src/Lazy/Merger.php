<?php

namespace SFW\Lazy;

/**
 * JS and CSS merger.
 */
class Merger extends \SFW\Lazy
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
     * Get info about merged files.
     */
    public function get(): array
    {
        if (($items = @scandir($this->dir)) !== false) {
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..') {
                    return ['prefix' => (int) $item];
                }
            }
        }

        return ['prefix' => false];
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
            if (($items = scandir($dir)) !== false) {
                foreach ($items as $item) {
                    if (preg_match('/\.js$/', $item)) {
                        $sections['js'][$this->dir . "%s.script.%s"][$name][] = $dir . $item;
                    }
                }
            }
        }

        foreach ($this->cssDir as $name => $dir) {
            if (($items = scandir($dir)) !== false) {
                foreach ($items as $item) {
                    if (preg_match('/^([a-z])(?:\..*)?\.css$/', $item, $M)) {
                        $sections['css'][$this->dir . "%s.styles.{$M[1]}.%s"][$name][] = $dir . $item;
                    }
                }
            }
        }

        $prefix = $this->get()['prefix'];

        if ($sections) {
            if ($prefix !== false) {
                foreach (array_keys($sections) as $section) {
                    foreach (array_keys($sections[$section]) as $pattern) {
                        foreach ($sections[$section][$pattern] as $name => $files) {
                            foreach ($files as $file) {
                                if ((int) filemtime($file) > $prefix) {
                                    $prefix = false;

                                    break 4;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $prefix = false;
        }

        if ($prefix === false) {
            $this->dir()->recreate($this->dir);

            if ($sections) {
                $prefix = time();

                foreach (array_keys($sections) as $section) {
                    foreach (array_keys($sections[$section]) as $pattern) {
                        foreach ($sections[$section][$pattern] as $name => $files) {
                            $sections[$section][$pattern][$name] = $this->{$section}($files);
                        }

                        $sections[$section][$pattern][$section] = implode("\n", $sections[$section][$pattern]);

                        foreach ($sections[$section][$pattern] as $name => $merged) {
                            if ($this->file()->put(sprintf($pattern, $prefix, $name), $merged) === false) {
                                $this->abend()->error();
                            }
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
                        $data = @$this->file()->get('public' . $M[1]);
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
