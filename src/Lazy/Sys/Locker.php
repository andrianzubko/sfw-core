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
     * Lock.
     */
    public function lock(string $key): bool
    {
        $file = str_replace('{KEY}', $key,
            self::$config['sys']['locker']['file']
        );

        if ($this->sys('Dir')->create(dirname($file)) === false) {
            $this->sys('Response')->error();
        }

        $handle = fopen($file, 'cb+');

        if ($handle === false
            || flock($handle, LOCK_EX | LOCK_NB) === false
        ) {
            return false;
        }

        $this->locks[$key] = $handle;

        return true;
    }

    /**
     * Unlock.
     */
    public function unlock(string $key): void
    {
        if (!isset($this->locks[$key])) {
            return;
        }

        fclose($this->locks[$key]);

        unset($this->locks[$key]);
    }
}
