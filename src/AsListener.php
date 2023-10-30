<?php

namespace SFW;

/**
 * Registers listener.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsListener
{
    /**
     * Registers listener.
     */
    public function __construct(public float $priority = 0.0)
    {
    }
}
