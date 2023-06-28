<?php

namespace SFW\Lazy\Sys;

/**
 * Default cache.
 */
class Cache extends \SFW\Lazy\Sys
{
    /**
     * Reinstanting class if called with argument.
     */
    public function __construct(protected ?string $cache = null) {}

    /**
     * Cache module instance.
     */
    public function getInstance(): \SFW\SimpleCacher\Cache
    {
        $cache = $this->cache ?? self::$config['cache'];

        return self::$sys->$cache();
    }
}
