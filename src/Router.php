<?php

namespace SFW;

/**
 * Abstraction for router.
 */
abstract class Router extends Base
{
    /**
     * Route from request url to entry point.
     */
    abstract public function get(): string|false;
}
