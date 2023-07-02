<?php

namespace SFW;

/**
 * Abstraction for all Notify classes.
 */
abstract class Notify extends Base
{
    /**
     * Build and return array of structures. This method called after browser disconnect.
     */
    public function build(Notify\Struct $defaultStruct): array
    {
        return [];
    }
}
