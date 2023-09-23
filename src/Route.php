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
        public string|array $method = []
    ) {
        $this->method = (array) $this->method;

        if ($this->method) {
            $this->method = array_map(strtoupper(...), $this->method);
        } else {
            $this->method[] = '';
        }
    }
}
