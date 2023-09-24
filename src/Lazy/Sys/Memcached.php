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
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

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
