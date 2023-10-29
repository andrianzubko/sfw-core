<?php

namespace SFW;

/**
 * Registers listener abstraction.
 */
abstract class AsListener
{
    /**
     * Registers listener.
     */
    public function __construct(public float $priority = 0.0)
    {
    }
}
