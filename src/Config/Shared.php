<?php

namespace App\Config;

/**
 * Your config (available from everywhere).
 */
class Shared extends \SFW\Config
{
    /**
     * Returns array with config parameters.
     */
    public static function get(): array
    {
        $shared = [];

        // {{{ etc

        /* Allow robots.
         *
         * bool
         */
        $shared['robots'] = false;

        /* Application name.
         *
         * string
         */
        $shared['name'] = 'SFW';

        // }}}

        return $shared;
    }
}
