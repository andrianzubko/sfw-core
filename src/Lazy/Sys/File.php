<?php

namespace SFW\Lazy\Sys;

/**
 * Files functions.
 */
class File extends \SFW\Lazy\Sys
{
    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

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
            && !self::sys('Dir')->create(dirname($file))
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
    public function putVar(string $file, mixed $variable, int $flags = 0, bool $createDir = true): bool
    {
        $success = $this->put($file,
            sprintf(
                "<?php\n\nreturn %s;\n",
                    var_export($variable, true)
            ), $flags, $createDir
        );

        if ($success
            && extension_loaded('zend-opcache')
        ) {
            opcache_invalidate($file, true);
        }

        return $success;
    }

    /**
     * File removing.
     */
    public function remove(string $file): bool
    {
        if (is_file($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * File coping.
     */
    public function copy(string $source, string $target, bool $createDir = true): bool
    {
        if ($createDir
            && !self::sys('Dir')->create(dirname($target))
                || !copy($source, $target)
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
            && !self::sys('Dir')->create(dirname($target))
                || !rename($source, $target)
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

        $imageSize = @getimagesize($file) ?: [];

        return [
            'name' => basename($file),

            'size' => $stat['size'] ?? 0,

            'w' => $imageSize[0] ?? 0,

            'h' => $imageSize[1] ?? 0,

            'mime' => $imageSize['mime'] ?? false,

            'modified' => $stat['mtime'] ?? 0,

            'created' => $stat['ctime'] ?? 0,

            'exists' => isset($stat['ctime']),
        ];
    }
}
