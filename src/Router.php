<?php

namespace SFW;

/**
 * Router to point.
 */
abstract class Router extends Base
{
    /**
     * Route from request url to starting point.
     */
    abstract public function get(): string|false;
}
