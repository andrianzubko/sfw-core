<?php

namespace SFW\Lazy;

/**
 * Base abstraction, nested by all notifies.
 */
abstract class Notify extends \SFW\Lazy
{
    /**
     * Abstraction for notify preparing.
     */
    abstract public function prepare(array $defaults): array;
}
