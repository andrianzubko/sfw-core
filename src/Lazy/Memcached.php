<?php

namespace SFW\Lazy;

/**
 * Memcached.
 */
class Memcached extends \SFW\Lazy
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
