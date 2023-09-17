<?php

namespace SFW;

/**
 * Abstraction for Lazy classes.
 */
abstract class Lazy extends Base
{
    /**
     * Each Lazy class can be turned into some other class if needed.
     *
     * @internal
     */
    public function getInstance(): object
    {
        return $this;
    }
}
