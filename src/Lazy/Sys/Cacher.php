<?php

namespace SFW\Lazy\Sys;

/**
 * Default cacher.
 *
 * @mixin \SFW\Cacher\Driver
 */
class Cacher extends \SFW\Lazy\Sys
{
    /**
     * Cacher module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Cacher\Driver
    {
        return $this->sys(self::$config['sys']['cacher']['default']);
    }
}
