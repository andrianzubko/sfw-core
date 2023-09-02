<?php

namespace SFW\Lazy\Sys;

/**
 * Redis.
 *
 * @mixin \SFW\Cacher\Driver
 */
class Redis extends \SFW\Lazy\Sys
{
    /**
     * Redis module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Cacher\Driver
    {
        return new \SFW\Cacher\Redis(self::$config['sys']['cacher']['redis']);
    }
}
