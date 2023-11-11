<?php

declare(strict_types=1);

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
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct() {}

    /**
     * Locks file by key and returns true or returns false if file already in use.
     *
     * @throws \SFW\Exception\Logic
     * @throws \SFW\Exception\Runtime
     */
    public function lock(string $key): bool
    {
        if (isset($this->locks[$key])) {
            throw new \SFW\Exception\Logic("Lock with key '$key' is already in use");
        }

        $file = str_replace('{KEY}', $key, self::$sys['config']['locker_file']);

        if (!self::sys('Dir')->create(dirname($file))) {
            throw new \SFW\Exception\Runtime(
                sprintf('Unable to create directory %s', dirname($file))
            );
        }

        $handle = fopen($file, 'cb+');

        if ($handle === false) {
            throw new \SFW\Exception\Runtime("Unable to open file $file");
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            return false;
        }

        $this->locks[$key] = $handle;

        return true;
    }

    /**
     * Unlocks file by key.
     *
     * @throws \SFW\Exception\Logic
     * @throws \SFW\Exception\Runtime
     */
    public function unlock(string $key): void
    {
        if (!isset($this->locks[$key])) {
            throw new \SFW\Exception\Logic("Lock with key '$key' is not exists");
        }

        if (!fclose($this->locks[$key])) {
            throw new \SFW\Exception\Runtime(
                sprintf('Unable to close file %s', $this->locks[$key])
            );
        }

        unset($this->locks[$key]);
    }
}
