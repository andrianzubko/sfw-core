<?php

namespace SFW\Router;

/**
 * Route from command line arguments to Command class.
 */
class Command extends \SFW\Router
{
    /**
     * Gets target class name.
     *
     * Very poor implementation. Will be better soon.
     */
    public function get(): array
    {
        if (isset($_SERVER['argv'][1])) {
            $class = preg_replace_callback(
                '/(?:^|:)(.)/', fn($M) => strtoupper($M[1]), $_SERVER['argv'][1]
            );

            return ["App\\Command\\$class", '__construct', $class];
        }

        return [false, false, false];
    }
}
