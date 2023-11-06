<?php

declare(strict_types=1);

namespace SFW;

/**
 * Abstraction for Notify classes.
 */
abstract class Notify extends Base
{
    /**
     * This method will be called after browser disconnect as last shutdown function.
     */
    abstract public function send(): void;
}
