<?php

namespace SFW\Lazy\Sys;

/**
 * Default cache.
 *
 * @mixin \SFW\SimpleCacher\Cache
 */
class Cache extends \SFW\Lazy\Sys
{
    /**
     * Reinstanting class if called with argument.
     */
    public function __construct(protected ?string $cache = null) {}

    /**
     * Cache module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\SimpleCacher\Cache
    {
        return $this->sys($this->cache ?? self::$config['sys']['cache']);
    }
}
