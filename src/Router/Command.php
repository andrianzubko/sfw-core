<?php

namespace SFW\Router;

/**
 * Routes from command line arguments to Command action.
 */
final class Command extends \SFW\Router
{
    /**
     * Gets class, method and action names.
     *
     * Very poor implementation. Will be better soon.
     */
    public function getTarget(): object|false
    {
        if (!isset($_SERVER['argv'][1])) {
            return false;
        }

        $target = (object) [];

        $target->action = preg_replace_callback('/(?:^|:)(.)/', fn($M) => strtoupper($M[1]), $_SERVER['argv'][1]);

        $target->class = "App\\Command\\$target->action";

        $target->method = '__construct';

        return $target;
    }
}
