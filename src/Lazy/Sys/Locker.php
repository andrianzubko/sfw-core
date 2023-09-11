<?php

namespace SFW\Lazy\Sys;

use SFW\Exception;

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
     * Lock.
     *
     * @throws RuntimeException
     */
    public function lock(string $key): bool
    {
        $file = str_replace('{KEY}', $key, self::$config['sys']['locker']['file']);

        $dir = dirname($file);

        if ($this->sys('Dir')->create($dir) === false) {
            throw new RuntimeException("Unable to create directory $dir");
        }

        $handle = fopen($file, 'cb+');

        if ($handle === false) {
            throw new RuntimeException("Unable to open file $file");
        }

        if (flock($handle, LOCK_EX | LOCK_NB) === false) {
            return false;
        }

        $this->locks[$key] = $handle;

        return true;
    }

    /**
     * Unlock.
     *
     * @throws RuntimeException
     */
    public function unlock(string $key): void
    {
        if (!isset($this->locks[$key])) {
            return;
        }

        if (fclose($this->locks[$key]) === false) {
            throw new RuntimeException("Unable to close file {$this->locks[$key]}");
        }

        unset($this->locks[$key]);
    }
}
