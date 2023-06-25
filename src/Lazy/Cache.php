<?php

namespace SFW\Lazy;

/**
 * Default cache.
 */
class Cache extends \SFW\Lazy
{
    /**
     * Reinstanting class if called with argument.
     */
    public function __construct(protected ?string $cache = null) {}

    /**
     * Cache module instance.
     */
    public function getInstance(): object
    {
        $cache = $this->cache ?? self::$config['cache'];

        return $this->{$cache}();
    }
}
