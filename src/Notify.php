<?php

namespace SFW;

/**
 * Abstraction for all Notify classes.
 */
abstract class Notify extends Base
{
    /**
     * Builds and yields or returns structures.
     *
     * This method called after browser disconnect.
     */
    public function build(NotifyStruct $defaultStruct): iterable
    {
        return [];
    }
}
