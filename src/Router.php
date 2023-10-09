<?php

namespace SFW;

/**
 * Abstraction for routers.
 */
abstract class Router extends Base
{
    /**
     * Gets action, full class and method name.
     */
    abstract public static function getTarget(): array;
}
