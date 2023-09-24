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
    abstract protected function get(): array;
}
