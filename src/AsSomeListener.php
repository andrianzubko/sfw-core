<?php
declare(strict_types=1);

namespace SFW;

/**
 * Registers some listener.
 */
abstract class AsSomeListener
{
    /**
     * Registers some listener.
     */
    public function __construct(public float $priority = 0.0)
    {
    }
}
