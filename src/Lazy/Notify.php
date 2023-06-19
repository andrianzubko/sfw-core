<?php

namespace SFW\Lazy;

/**
 * Base abstraction, nested by all notifies.
 */
abstract class Notify extends \App\Lazy
{
    /**
     * Abstraction for notify preparing.
     */
    abstract public function prepare(array $defaults): void;

    /**
     * Abstraction for notify sending.
     */
    abstract public function send(): void;
}
