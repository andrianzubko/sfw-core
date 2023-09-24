<?php

namespace SFW;

/**
 * Router.
 */
abstract class Router extends Base
{
    /**
     * Gets full class name, method and action.
     */
    abstract public function get(): array;
}
