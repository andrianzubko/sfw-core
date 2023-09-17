<?php

namespace SFW;

/**
 * Abstraction for router.
 */
abstract class Router extends Base
{
    /**
     * Route from request url to Controller class.
     */
    abstract public function getController(): string|false;

    /**
     * Route from command line arguments to Command class.
     *
     * Very poor implementation. Will be better soon.
     */
    final public function getCommand(): string|false
    {
        if (!isset($_SERVER['argv'][1])) {
            return false;
        }

        return preg_replace_callback('/(?:^|:)(.)/',
            fn($M) => strtoupper($M[1]), $_SERVER['argv'][1]
        );
    }
}
