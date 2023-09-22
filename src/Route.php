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
        public string $url,
        public string|array $methods = []
    ) {
        $this->methods = (array) $this->methods;

        if ($this->methods) {
            $this->methods = array_map(strtoupper(...), $this->methods);
        } else {
            $this->methods = [''];
        }
    }
}
