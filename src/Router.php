<?php

namespace SFW;

/**
 * Abstraction for routers.
 */
abstract class Router extends Base
{
    /**
     * Gets class, method and action names.
     */
    abstract public static function getTarget(): array;
}
