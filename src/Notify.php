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
    public function build(\SFW\NotifyStruct $defaultStruct): array
    {
        return [];
    }
}
