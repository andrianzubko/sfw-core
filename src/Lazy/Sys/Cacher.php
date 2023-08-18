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
     * Reinstating class if called with argument.
     */
    public function __construct(protected ?string $cacher = null) {}

    /**
     * Cacher module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Cacher\Driver
    {
        return $this->sys($this->cacher ?? self::$config['sys']['cacher']['default']);
    }
}
