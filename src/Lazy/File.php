<?php

namespace SFW\Lazy;

/**
 * Files functions.
 */
class File extends \App\Lazy
{
    /**
     * File removing.
     */
    public function remove(string $target): bool
    {
        return unlink($target);
    }

    /**
     * Getting file contents into string.
     */
    public function get(string $target): string|false {
        return file_get_contents($target);
    }

    /**
     * Putting contents to file.
     */
    public function put(string $target, mixed $contents, int $flags = 0): bool
    {
        if ($this->dir()->create(dirname($target)) === false ||
                file_put_contents($target, $contents, $flags) === false) {

            return false;
        }

        @chmod($target, 0666);

        return true;
    }

    /**
     * File coping.
     */
    public function copy(string $source, string $target): bool
    {
        if ($this->dir()->create(dirname($target)) === false ||
                copy($source, $target) === false) {

            return false;
        }

        @chmod($target, 0666);

        return true;
    }

    /**
     * File moving.
     */
    public function move(string $source, string $target): bool
    {
        if ($this->dir()->create(dirname($target)) === false ||
                rename($source, $target) === false) {

            return false;
        }

        @chmod($target, 0666);

        return true;
    }

    /**
     * Getting some file statistics.
     */
    public function stats(string $file): array
    {
        $stat = @stat($file);

        $size = @getimagesize($file);

        return [
            'name' => basename($file),

            'size' => (int) @$stat['size'],

            'w' => (int) @$size[0],

            'h' => (int) @$size[1],

            'modified' => (int) @$stat['mtime'],

            'modified_localtime' => date('Y-m-d H:i:s', (int) @$stat['mtime']),
        ];
    }
}
