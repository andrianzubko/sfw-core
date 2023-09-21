<?php

namespace SFW;

/**
 * Router.
 */
abstract class Router extends Base
{
    /**
     * Gets target class name.
     */
    abstract public function get(): string|false;
}
