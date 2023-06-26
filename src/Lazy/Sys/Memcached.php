<?php

namespace SFW\Lazy\Sys;

/**
 * Memcached.
 */
class Memcached extends \SFW\Lazy\Sys
{
    /**
     * Just in case.
     */
    public function __construct() {}

    /**
     * Memcached module instance.
     */
    public function getInstance(): object
    {
        $options = self::$config['memcached'];

        $options['ns'] ??= md5(getcwd());

        return new \SFW\SimpleCacher\Memcached(...$options);
    }
}
