<?php

namespace SFW\Lazy\Sys;

/**
 * Memcached.
 */
class Memcached extends \SFW\Lazy\Sys
{
    /**
     * Memcached module instance.
     */
    public function getInstance(): \SFW\SimpleCacher\Cache
    {
        return new \SFW\SimpleCacher\Memcached(...self::$config['sys']['memcached']);
    }
}
