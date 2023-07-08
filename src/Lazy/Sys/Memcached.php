<?php

namespace SFW\Lazy\Sys;

/**
 * Memcached.
 *
 * @mixin \SFW\SimpleCacher\Cache
 */
class Memcached extends \SFW\Lazy\Sys
{
    /**
     * Memcached module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\SimpleCacher\Cache
    {
        return new \SFW\SimpleCacher\Memcached(
            self::$config['sys']['cache_memcached_ttl'],
            self::$config['sys']['cache_memcached_ns'],
            self::$config['sys']['cache_memcached_options'],
            self::$config['sys']['cache_memcached_servers']
        );
    }
}
