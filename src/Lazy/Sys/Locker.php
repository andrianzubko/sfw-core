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
     * Lock or return false.
     */
    public function lock(string $key): bool
    {
        $file = sprintf(self::$config['sys']['lock_files_pattern'], $key);

        if ($this->sys('Dir')->create(dirname($file)) === false) {
            $this->sys('Abend')->error();
        }

        $fh = fopen($file, 'a+');

        if ($fh === false
            || flock($fh, LOCK_EX | LOCK_NB) === false
        ) {
            return false;
        }

        $this->locks[$key] = $fh;

        return true;
    }

    /**
     * Unlock.
     */
    public function unlock(string $key): void
    {
        if (isset($this->locks[$key])) {
            fclose($this->locks[$key]);

            unset($this->locks[$key]);
        }
    }
}
