<?php

namespace SFW;

/**
 * Abstraction for Notify classes.
 */
abstract class Notify extends Base
{
    /**
     * Builds and yields or returns structures.
     *
     * This method called after browser disconnect as last shutdown function.
     */
    public function build(NotifyStruct $defaultStruct): iterable
    {
        return [];
    }
}
