<?php

namespace SFW\Lazy\Sys;

/**
 * APC.
 *
 * @mixin \SFW\Cacher\Driver
 */
class Apc extends \SFW\Lazy\Sys
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
     * APC module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Cacher\Driver
    {
        return new \SFW\Cacher\Apc(self::$config['sys']['cacher']['apc']);
    }
}
