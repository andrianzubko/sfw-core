<?php

namespace SFW;

/**
 * Abstraction for routers.
 */
abstract class Router extends Base
{
    /**
     * Gets full class name, method and action.
     */
    abstract public function getAction(): array;
}
