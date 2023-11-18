<?php

declare(strict_types=1);

namespace SFW;

/**
 * Registers listener (can be called many times).
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsListener
{
    /**
     * Registers some listener.
     */
    public function __construct(public float $priority = 0.0) {}
}
