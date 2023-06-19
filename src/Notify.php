<?php

namespace SFW;

/**
 * Abstraction for all notifies.
 */
abstract class Notify extends Base
{
    /**
     * Here we can work with database, but all hevy work must be done in finish() method.
     */
    abstract public function prepare(): void;

    /**
     * Fill and return array of structures. This method called after browser disconnect.
     */
    abstract public function finish(array $defaults): array;
}
