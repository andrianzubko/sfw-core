<?php

namespace SFW\Lazy;

/**
 * APC.
 */
class Apc extends \SFW\Lazy
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
