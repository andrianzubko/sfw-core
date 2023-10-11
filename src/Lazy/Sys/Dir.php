<?php

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
    public function __construct()
    {
    }

    /**
     * Directory scanning.
     */
    public function scan(
        string $dir,
        bool $recursive = false,
        bool $withDir = false,
        int $order = SCANDIR_SORT_ASCENDING
    ): array {
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
                    $scanned[] = $withDir
                        ? "$dir/$item"
                        : "$item";
                } else {
                    foreach ($this->scan("$dir/$item", true, false, $order) as $subItem) {
                        $scanned[] = $withDir
                            ? "$dir/$item/$subItem"
                            : "$item/$subItem";
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
                } elseif (!self::sys('File')->copy("$source/$item", "$target/$item", false)) {
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
