<?php

namespace SFW\Lazy\Sys;

/**
 * Locker.
 */
class Locker extends \SFW\Lazy\Sys
{
    /**
     * Already created locks.
     */
    protected array $locks = [];

    /**
     * Lock and return contents or false if can't.
     */
    public function lock(string $key): string|false
    {
        $file = sprintf(self::$config['sys']['locker']['pattern'], $key);

        if ($this->sys('Dir')->create(dirname($file)) === false) {
            $this->sys('Abend')->error();
        }

        $fh = fopen($file, 'c+');

        if ($fh === false
            || flock($fh, LOCK_EX | LOCK_NB) === false
        ) {
            return false;
        }

        $this->locks[$key] = $fh;

        $size = filesize($file);

        if ($size > 0) {
            return fread($fh, $size);
        }

        return '';
    }

    /**
     * Unlock and optionaly save contents to lock file.
     */
    public function unlock(string $key, ?string $contents = null): void
    {
        if (!isset($this->locks[$key])) {
            return;
        }

        $fh = $this->locks[$key];

        ftruncate($fh, 0);

        if (isset($contents)) {
            rewind($fh);

            fwrite($fh, $contents);
        }

        fclose($fh);

        unset($this->locks[$key]);
    }
}
