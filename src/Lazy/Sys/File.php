<?php

namespace SFW\Lazy\Sys;

/**
 * Files functions.
 */
class File extends \SFW\Lazy\Sys
{
    /**
     * Getting file contents into string.
     */
    public function get(string $file): string|false {
        return file_get_contents($file);
    }

    /**
     * Putting contents to file.
     */
    public function put(string $file, mixed $contents, int $flags = 0, bool $createDir = true): bool
    {
        if ($createDir
            && $this->sys('Dir')->create(dirname($file)) === false
                || file_put_contents($file, $contents, $flags) === false
        ) {
            return false;
        }

        @chmod($file, self::$config['sys']['file']['mode']);

        return true;
    }

    /**
     * Putting variable to some PHP file.
     */
    public function putVar(string $file, mixed $var): bool
    {
        $var = var_export($var, true);

        $success = $this->put($file, "<?php\n\nreturn $var;\n");

        if ($success) {
            opcache_invalidate($file, true);
        }

        return $success;
    }

    /**
     * File removing.
     */
    public function remove(string $file): bool
    {
        return unlink($file);
    }

    /**
     * File coping.
     */
    public function copy(string $source, string $target, bool $createDir = true): bool
    {
        if ($createDir
            && $this->sys('Dir')->create(dirname($target)) === false
                || copy($source, $target) === false
        ) {
            return false;
        }

        @chmod($target, self::$config['sys']['file']['mode']);

        return true;
    }

    /**
     * File moving.
     */
    public function move(string $source, string $target, bool $createDir = true): bool
    {
        if ($createDir
            && $this->sys('Dir')->create(dirname($target)) === false
                || rename($source, $target) === false
        ) {
            return false;
        }

        return true;
    }

    /**
     * Getting some file statistics.
     */
    public function stats(string $file): array
    {
        $stat = @stat($file) ?: [];

        $imagesize = @getimagesize($file) ?: [];

        return [
            'name' => basename($file),

            'size' => $stat['size'] ?? 0,

            'w' => $imagesize[0] ?? 0,

            'h' => $imagesize[1] ?? 0,

            'mime' => $imagesize['mime'] ?? false,

            'modified' => $stat['mtime'] ?? 0,

            'created' => $stat['ctime'] ?? 0,

            'exists' => isset($stat['ctime']),
        ];
    }
}
