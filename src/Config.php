<?php

namespace SFW;

/**
 * Abstraction for all Config classes.
 */
abstract class Config
{
    /**
     * Returns array with config parameters.
     */
    abstract public function get(): array;
}
