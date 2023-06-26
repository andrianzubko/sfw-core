<?php

namespace SFW\Lazy\Sys;

/**
 * APC.
 */
class Apc extends \SFW\Lazy\Sys
{
    /**
     * Just in case.
     */
    public function __construct() {}

    /**
     * APC module instance.
     */
    public function getInstance(): object
    {
        $options = self::$config['apc'];

        $options['ns'] ??= md5(getcwd());

        return new \SFW\SimpleCacher\Apc(...$options);
    }
}
