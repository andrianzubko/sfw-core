<?php

namespace SFW\Lazy\Sys;

/**
 * APC.
 *
 * @mixin \SFW\SimpleCacher\Cache
 */
class Apc extends \SFW\Lazy\Sys
{
    /**
     * APC module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\SimpleCacher\Cache
    {
        return new \SFW\SimpleCacher\Apc(...self::$config['sys']['cache']['apc']);
    }
}
