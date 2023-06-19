<?php

namespace SFW\Lazy;

/**
 * Locker.
 */
class Locker extends \App\Lazy
{
    /**
     * Lock files pattern.
     */
    protected string $lock = 'locks/%s.lock';

    /**
     * Already created locks.
     */
    protected array $locks = [];

    /**
     * Locking or return with false.
     */
    public function lock(string $key): bool
    {
        $this->dir()->create(dirname($this->lock));

        $fh = fopen(sprintf($this->lock, $key), 'a+');

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
