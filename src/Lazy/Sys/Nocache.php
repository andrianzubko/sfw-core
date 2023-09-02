<?php

namespace SFW\Lazy\Sys;

/**
 * Nocache.
 *
 * @mixin \SFW\Cacher\Driver
 */
class Nocache extends \SFW\Lazy\Sys
{
    /**
     * Nocache module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Cacher\Driver
    {
        return new \SFW\Cacher\Nocache();
    }
}
