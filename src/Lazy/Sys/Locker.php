<?php

namespace SFW\Lazy\Sys;

use SFW\Exception\{Logic, Runtime};

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
    public function __construct()
    {
    }

    /**
     * Locks file by key and returns true or returns false if file already in use.
     *
     * @throws Logic
     * @throws Runtime
     */
    public function lock(string $key): bool
    {
        if (isset($this->locks[$key])) {
            throw new Logic("Lock with key '$key' is already in use");
        }

        $file = str_replace('{KEY}', $key, self::$config['sys']['locker_file']);

        if (!self::sys('Dir')->create(dirname($file))) {
            throw new Runtime(sprintf('Unable to create directory %s', dirname($file)));
        }

        $handle = fopen($file, 'cb+');

        if ($handle === false) {
            throw new Runtime("Unable to open file $file");
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
     * @throws Logic
     * @throws Runtime
     */
    public function unlock(string $key): void
    {
        if (!isset($this->locks[$key])) {
            throw new Logic("Lock with key '$key' is not exists");
        }

        if (!fclose($this->locks[$key])) {
            throw new Runtime(sprintf('Unable to close file %s', $this->locks[$key]));
        }

        unset($this->locks[$key]);
    }
}
