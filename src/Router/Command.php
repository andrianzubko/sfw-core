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
    public function getTarget(): array
    {
        if (isset($_SERVER['argv'][1])) {
            $action = preg_replace_callback(
                '/(?:^|:)(.)/', fn($M) => strtoupper($M[1]), $_SERVER['argv'][1]
            );

            return [$action, "App\\Command\\$action", '__construct'];
        }

        return [false, false, false];
    }
}
