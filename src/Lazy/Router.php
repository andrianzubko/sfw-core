<?php

namespace SFW\Lazy;

/**
 * Router to point.
 */
class Router extends \SFW\Lazy
{
    /**
     * Route from request url to starting point.
     */
    public function getPoint(): string|false
    {
        return false;
    }
}
