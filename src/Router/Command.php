<?php

namespace SFW\Router;

/**
 * Routes from command line arguments to Command action.
 */
class Command extends \SFW\Router
{
    /**
     * Gets class, method and action names.
     *
     * Very poor implementation. Will be better soon.
     */
    public static function getTarget(): object|false
    {
        if (isset($_SERVER['argv'][1])) {
            $target = (object) [];

            $target->action = preg_replace_callback('/(?:^|:)(.)/', fn($M) => strtoupper($M[1]), $_SERVER['argv'][1]);

            $target->class = "App\\Command\\$target->action";

            $target->method = '__construct';

            return $target;
        }

        return false;
    }
}
