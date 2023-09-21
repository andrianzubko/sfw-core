<?php

namespace SFW;

/**
 * Registers route.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Route
{
    /**
     * Registers route.
     */
    public function __construct(
        public string $path,
        public string|array $methods = []
    ) {
        $this->methods = array_map(strtoupper(...), (array) $this->methods);
    }
}
