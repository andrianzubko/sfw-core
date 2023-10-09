<?php

namespace SFW\Router;

/**
 * Routes from command line arguments to Command action.
 */
class Command extends \SFW\Router
{
    /**
     * Gets action, full class and method name.
     *
     * Very poor implementation. Will be better soon.
     */
    public static function getTarget(): array
    {
        if (isset($_SERVER['argv'][1])) {
            $action = preg_replace_callback('/(?:^|:)(.)/',
                fn($M) => strtoupper($M[1]), $_SERVER['argv'][1]
            );

            return ["App\\Command\\$action", '__construct', $action];
        }

        return [false, false, false];
    }
}
