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
        if (!is_dir($dir)) {
            $success = mkdir($dir, recursive: true);

            if ($success) {
                @chmod($dir, self::$config['sys']['dir']['mode']);
            }

            return $success;
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
                            continue;
                        }

                        if (is_dir("$dir/$item")) {
                            if ($this->remove("$dir/$item") === false) {
                                $status = false;
                            }
                        } elseif ($this->sys('File')->remove("$dir/$item") === false) {
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
    public function clear(string $dir, bool $recursive = true): bool
    {
        $status = true;

        if (is_dir($dir)) {
            if (($items = scandir($dir)) !== false) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }

                    if (is_dir("$dir/$item")) {
                        if ($this->remove("$dir/$item", $recursive) === false) {
                            $status = false;
                        }
                    } elseif ($this->sys('File')->remove("$dir/$item") === false) {
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

        if ($this->create($target) !== false
            && ($items = scandir($source)) !== false
        ) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                if (is_dir("$source/$item")) {
                    if ($this->copy("$source/$item", "$target/$item") === false) {
                        $status = false;
                    }
                } elseif ($this->sys('File')->copy("$source/$item", "$target/$item", false) === false) {
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

        return true;
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
