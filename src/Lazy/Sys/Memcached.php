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
     * Options for cacher.
     */
    protected array $options = [];

    /**
     * Initializes options for cacher.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    protected function __construct()
    {
        $this->options['ttl'] = self::$sys['config']['cacher_memcached_ttl'];

        $this->options['ns'] = self::$sys['config']['cacher_memcached_ns'];

        $this->options['servers'] = self::$sys['config']['cacher_memcached_servers'];

        $this->options['options'] = self::$sys['config']['cacher_memcached_options'];
    }

    /**
     * Memcached module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Cacher\Driver
    {
        return new \SFW\Cacher\Memcached((new static())->options);
    }
}
