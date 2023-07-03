<?php

namespace SFW\Lazy\Sys;

/**
 * Dir functions.
 */
class Dir extends \SFW\Lazy\Sys
{
    /**
     * For temporary directory.
     */
    protected ?string $temporary;

    /**
     * Directory scanning.
     */
    public function scan(string $dir, bool $recursive = false, int $order = SCANDIR_SORT_ASCENDING): array
    {
        $items = [];

        foreach ((scandir($dir, $order) ?: []) as $item) {
            if ($item === '.' || $item === '..') {

            } elseif (!$recursive || is_file("$dir/$item")) {
                $items[] = $item;
            } else {
                foreach ($this->scan("$dir/$item", true, $order) as $subitem) {
                    $items[] = "$item/$subitem";
                }
            }
        }

        return $items;
    }

    /**
     * Directory creation.
     */
    public function create(string $dir): bool
    {
        if (!is_dir($dir)) {
            return mkdir($dir, 0777, true);
        }

        return true;
    }

    /**
     * Directory removing.
     */
    public function remove(string $dir, bool $recursive = true): bool
    {
        $status = true;

        if (is_dir($dir)) {
            if ($recursive) {
                if (($items = scandir($dir)) !== false) {
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..') {

                        } elseif (is_dir("$dir/$item")) {
                            if ($this->remove("$dir/$item") === false) {
                                $status = false;
                            }
                        } elseif (unlink("$dir/$item") === false) {
                            $status = false;
                        }
                    }
                } else {
                    $status = false;
                }
            }

            if (rmdir($dir) === false) {
                $status = false;
            }
        }

        return $status;
    }

    /**
     * Directory clearing.
     */
    public function clear(string $dir): bool
    {
        $status = true;

        if (is_dir($dir)) {
            if (($items = scandir($dir)) !== false) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {

                    } elseif (is_dir("$dir/$item")) {
                        if ($this->remove("$dir/$item") === false) {
                            $status = false;
                        }
                    } elseif (unlink("$dir/$item") === false) {
                        $status = false;
                    }
                }
            } else {
                $status = false;
            }
        }

        return $status;
    }

    /**
     * Directory coping.
     */
    public function copy(string $source, string $target): bool
    {
        $status = true;

        if (($items = scandir($source)) !== false
            && $this->create($target) !== false
        ) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {

                } elseif (is_dir("$source/$item")) {
                    if ($this->copy("$source/$item", "$target/$item") === false) {
                        $status = false;
                    }
                } elseif (copy("$source/$item", "$target/$item") === false) {
                    $status = false;
                }
            }
        } else {
            $status = false;
        }

        return $status;
    }

    /**
     * Directory moving.
     */
    public function move(string $source, string $target): bool
    {
        if ($this->create(dirname($target)) === false
            || rename($source, $target) === false
        ) {
            return false;
        }

        @chmod($target, 0777);

        return true;
    }

    /**
     * Making temporary directory.
     */
    public function temporary(): string
    {
        if (!isset($this->temporary)) {
            $this->temporary = realpath(sys_get_temp_dir());
        }

        for ($i = 1; $i <= 10; $i++) {
            $dir = sprintf('%s/%s', $this->temporary, $this->sys('Text')->random());

            if (mkdir($dir, 0600, true)) {
                register_shutdown_function(
                    function () use ($dir): void {
                        $this->remove($dir);
                    }
                );

                return $dir;
            }
        }

        $this->sys('Abend')->error();
    }
}
