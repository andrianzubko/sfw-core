<?php

namespace SFW\Lazy\Sys;

/**
 * APC.
 */
class Apc extends \SFW\Lazy\Sys
{
    /**
     * APC module instance.
     */
    public function getInstance(): \SFW\SimpleCacher\Cache
    {
        return new \SFW\SimpleCacher\Apc(...self::$config['apc']);
    }
}
