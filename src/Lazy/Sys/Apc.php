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
     * APC module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Cacher\Driver
    {
        return new \SFW\Cacher\Apc(self::$config['sys']['cacher']['apc']);
    }
}
