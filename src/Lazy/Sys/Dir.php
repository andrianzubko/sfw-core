<?php

declare(strict_types=1);

namespace SFW\Lazy\Sys;

/**
 * Dir functions.
 */
class Dir extends \SFW\Lazy\Sys
{
    /**
     * Temporary directory.
     */
    protected string $tempDir;

    /**
     * Created temporary subdirectories (for cleaner).
     */
    protected array $tempSubDirs = [];

    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct() {}

    /**
     * Directory scanning.
     */
    public function scan(
        string $dir,
        bool $recursive = false,
        bool $withDir = false,
        int $order = SCANDIR_SORT_ASCENDING,
    ): array {
        if (!is_dir($dir)) {
            return [];
        }

        $items = scandir($dir, $order);

        if ($items === false) {
            return [];
        }

        $scanned = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (!$recursive || is_file("$dir/$item")) {
                if ($withDir) {
                    $scanned[] = "$dir/$item";
                } else {
                    $scanned[] = $item;
                }
            } else {
                foreach ($this->scan("$dir/$item", true, false, $order) as $subItem) {
                    if ($withDir) {
                        $scanned[] = "$dir/$item/$subItem";
                    } else {
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
            @chmod($dir, self::$sys['config']['dir_mode']);
        }

        return $success;
    }

    /**
     * Directory removing.
     */
    public function remove(string $dir, bool $recursive = true): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $success = true;

        if ($recursive) {
            $items = scandir($dir);

            if ($items === false) {
                return false;
            }

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
        }

        if ($success) {
            return rmdir($dir);
        }

        return false;
    }

    /**
     * Directory clearing.
     */
    public function clear(string $dir, bool $recursive = true): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $items = scandir($dir);

        if ($items === false) {
            return false;
        }

        $success = true;

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

        return $success;
    }

    /**
     * Directory coping.
     */
    public function copy(string $source, string $target): bool
    {
        if (!$this->create($target)) {
            return false;
        }

        $items = scandir($source);

        if ($items === false) {
            return false;
        }

        $success = true;

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (is_dir("$source/$item")) {
                if (!$this->copy("$source/$item", "$target/$item")) {
                    $success = false;
                }
            } elseif (!self::sys('File')->copy("$source/$item", "$target/$item", false)) {
                $success = false;
            }
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
     * @throws \SFW\Exception\Runtime;
     */
    public function temporary(): string
    {
        if (!isset($this->tempDir)) {
            $tempDir = realpath(sys_get_temp_dir());

            if ($tempDir === false) {
                throw new \SFW\Exception\Runtime('Invalid system temporary directory');
            }

            $this->tempDir = $tempDir;

            register_shutdown_function(
                function () {
                    register_shutdown_function(
                        function () {
                            register_shutdown_function(
                                function () {
                                    foreach ($this->tempSubDirs as $dir) {
                                        $this->remove($dir);
                                    }
                                }
                            );
                        }
                    );
                }
            );
        }

        for ($i = 1; $i <= 7; $i++) {
            $tempSubDir = $this->tempDir . '/' . self::sys('Text')->random();

            if (@mkdir($tempSubDir, 0600, true)) {
                $this->tempSubDirs[] = $tempSubDir;

                return $tempSubDir;
            }
        }

        throw new \SFW\Exception\Runtime('Unable to create temporary subdirectory');
    }
}
