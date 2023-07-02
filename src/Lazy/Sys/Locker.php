<?php

namespace SFW\Lazy\Sys;

/**
 * Locker.
 */
class Locker extends \SFW\Lazy\Sys
{
    /**
     * Lock files pattern.
     */
    protected string $pattern = APP_DIR . '/locks/%s.lock';

    /**
     * Already created locks.
     */
    protected array $locks = [];

    /**
     * Locking or return with false.
     */
    public function lock(string $key): bool
    {
        self::$sys->dir()->create(dirname($this->pattern));

        $fh = fopen(sprintf($this->pattern, $key), 'a+');

        if ($fh === false || flock($fh, LOCK_EX | LOCK_NB) === false) {
            return false;
        }

        $this->locks[$key] = $fh;

        return true;
    }

    /**
     * Unlocking.
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
