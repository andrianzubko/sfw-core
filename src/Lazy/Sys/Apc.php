<?php

namespace SFW\Lazy\Sys;

/**
 * APC.
 *
 * @mixin \SFW\Cacher\Driver
 */
class Apc extends \SFW\Lazy\Sys
{
    /**
     * Options for cacher.
     */
    protected array $options = [];

    /**
     * Initializes options for cacher.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    protected function __construct()
    {
        $this->options['ttl'] = self::$sys['config']['cacher_apc_ttl'];

        $this->options['ns'] = self::$sys['config']['cacher_apc_ns'];
    }

    /**
     * APC module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Cacher\Driver
    {
        return new \SFW\Cacher\Apc((new static())->options);
    }
}
