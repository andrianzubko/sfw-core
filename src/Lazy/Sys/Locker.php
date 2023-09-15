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
    protected static array $locks = [];

    /**
     * Lock.
     *
     * @throws \SFW\LogicException
     * @throws \SFW\RuntimeException
     */
    public function lock(string $key): bool
    {
        if (isset(self::$locks[$key])) {
            throw new \SFW\LogicException(
                sprintf(
                    'Lock with key %s is already in use',
                        $key
                )
            );
        }

        $file = str_replace('{KEY}', $key, self::$config['sys']['locker']['file']);

        $dir = dirname($file);

        if ($this->sys('Dir')->create($dir) === false) {
            throw new \SFW\RuntimeException(
                sprintf(
                    'Unable to create directory %s',
                        $dir
                )
            );
        }

        $handle = fopen($file, 'cb+');

        if ($handle === false) {
            throw new \SFW\RuntimeException(
                sprintf(
                    'Unable to open file %s',
                        $file
                )
            );
        }

        if (flock($handle, LOCK_EX | LOCK_NB) === false) {
            return false;
        }

        self::$locks[$key] = $handle;

        return true;
    }

    /**
     * Unlock.
     *
     * @throws \SFW\LogicException
     * @throws \SFW\RuntimeException
     */
    public function unlock(string $key): void
    {
        if (!isset(self::$locks[$key])) {
            throw new \SFW\LogicException(
                sprintf(
                    'Lock with key %s is not exists',
                        $key
                )
            );
        }

        if (fclose(self::$locks[$key]) === false) {
            throw new \SFW\RuntimeException(
                sprintf(
                    'Unable to close file %s',
                        self::$locks[$key]
                )
            );
        }

        unset(self::$locks[$key]);
    }
}
