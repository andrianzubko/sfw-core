<?php

namespace SFW;

/**
 * Abstraction for all notifies.
 */
abstract class Notify extends Base
{
    /**
     * Build and return array of structures. This method called after browser disconnect.
     */
    abstract public function build(array $defaults): array;
}
