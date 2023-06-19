<?php

namespace SFW;

/**
 * Abstraction for all notifies.
 */
abstract class Notify extends Base
{
    /**
     * Fill and return array of structures. This method called after browser disconnect.
     */
    abstract public function finish(array $defaults): array;
}
