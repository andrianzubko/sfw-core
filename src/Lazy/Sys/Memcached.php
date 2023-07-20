<?php

namespace SFW\Lazy\Sys;

/**
 * Memcached.
 *
 * @mixin \SFW\Cacher\Driver
 */
class Memcached extends \SFW\Lazy\Sys
{
    /**
     * Memcached module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Cacher\Driver
    {
        return new \SFW\Cacher\Memcached(self::$config['sys']['cacher']['memcached']);
    }
}
