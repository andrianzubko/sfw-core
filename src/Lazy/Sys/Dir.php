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
    protected static ?string $temporary;

    /**
     * Directory scanning.
     */
    public function scan(string $dir, bool $recursive = false, int $order = SCANDIR_SORT_ASCENDING): array
    {
        $scanned = [];

        if (is_dir($dir)
            && ($items = scandir($dir, $order)) !== false
        ) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                if (!$recursive
                    || is_file("$dir/$item")
                ) {
                    $scanned[] = $item;
                } else {
                    foreach ($this->scan("$dir/$item", true, $order) as $subItem) {
                        $scanned[] = "$item/$subItem";
                    }
                }
            }
        }

        return $scanned;
    }

    /**
     * Directory creation.
     */
    public function create(string $dir): bool
    {
        if (is_dir($dir)) {
            return true;
        }

        $success = mkdir($dir, recursive: true);

        if ($success) {
            @chmod($dir, self::$config['sys']['dir']['mode']);
        }

        return $success;
    }

    /**
     * Directory removing.
     */
    public function remove(string $dir, bool $recursive = true): bool
    {
        $success = true;

        if (is_dir($dir)) {
            if ($recursive) {
                if (($items = scandir($dir)) !== false) {
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..') {
                            continue;
                        }

                        if (is_dir("$dir/$item")) {
                            if (!$this->remove("$dir/$item")) {
                                $success = false;
                            }
                        } elseif (!unlink("$dir/$item")) {
                            $success = false;
                        }
                    }
                } else {
                    $success = false;
                }
            }

            if ($success && !rmdir($dir)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Directory clearing.
     */
    public function clear(string $dir, bool $recursive = true): bool
    {
        $success = true;

        if (is_dir($dir)) {
            if (($items = scandir($dir)) !== false) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }

                    if (is_dir("$dir/$item")) {
                        if (!$this->remove("$dir/$item", $recursive)) {
                            $success = false;
                        }
                    } elseif (!unlink("$dir/$item")) {
                        $success = false;
                    }
                }
            } else {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Directory coping.
     */
    public function copy(string $source, string $target): bool
    {
        $success = true;

        if ($this->create($target)
            && ($items = scandir($source)) !== false
        ) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                if (is_dir("$source/$item")) {
                    if (!$this->copy("$source/$item", "$target/$item")) {
                        $success = false;
                    }
                } elseif (!$this->sys('File')->copy("$source/$item", "$target/$item", false)) {
                    $success = false;
                }
            }
        } else {
            $success = false;
        }

        return $success;
    }

    /**
     * Directory moving.
     */
    public function move(string $source, string $target): bool
    {
        return $this->create(dirname($target)) && rename($source, $target);
    }

    /**
     * Makes temporary directory.
     *
     * @throws \SFW\RuntimeException;
     */
    public function temporary(): string
    {
        if (!isset(self::$temporary)) {
            self::$temporary = realpath(sys_get_temp_dir());
        }

        for ($i = 1; $i <= 7; $i++) {
            $dir = sprintf('%s/%s', self::$temporary, $this->sys('Text')->random());

            if (@mkdir($dir, 0600, true)) {
                register_shutdown_function(
                    function () use ($dir): void {
                        $this->remove($dir);
                    }
                );

                return $dir;
            }
        }

        throw new \SFW\RuntimeException('Unable to create temporary directory');
    }
}
