<?php

namespace SFW;

/**
 * Basic abstract for all Point classes.
 */
abstract class Point extends Base
{
    /**
     * Abstraction for automaticly called method.
     */
    abstract public function main(): void;
}
