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
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Redis module instance.
     *
     * @throws \SFW\Cacher\Exception
     *
     * @internal
     */
    public function getInstance(): \SFW\Cacher\Driver
    {
        return new \SFW\Cacher\Redis(self::$config['sys']['cacher']['redis']);
    }
}
