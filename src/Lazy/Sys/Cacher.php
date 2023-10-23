<?php

namespace SFW\Lazy\Sys;

/**
 * Default cacher.
 *
 * @mixin \SFW\Cacher\Driver
 */
class Cacher extends \SFW\Lazy\Sys
{
    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Cacher module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Cacher\Driver
    {
        return self::sys(self::$sys['config']['cacher_default']);
    }
}
