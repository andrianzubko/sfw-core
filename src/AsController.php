<?php

declare(strict_types=1);

namespace SFW;

/**
 * Registers controller.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsController
{
    /**
     * Registers controller.
     */
    public function __construct(
        public string|array $url,
        public string|array $method = [],
        public ?string $alias = null,
    ) {
        $this->url = (array) $this->url;

        $this->method = (array) $this->method;

        if ($this->method) {
            $this->method = array_map(strtoupper(...), $this->method);
        } else {
            $this->method[] = '';
        }
    }
}
