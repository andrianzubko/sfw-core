<?php

namespace App\Config;

/**
 * System config (not available from templates).
 *
 * Only override needed parameters from basic system config.
 * Don't add here your own new parameters!
 */
class Sys extends \SFW\Config
{
    /**
     * Returns array with config parameters.
     */
    public static function get(): array
    {
        $sys = \SFW\Config\Sys::get();

        // {{{ overrides

        $sys['env'] = 'dev';

        $sys['db']['pgsql']['connection'] = 'host=/var/run/postgresql port=49120 dbname=ondr-t1 user=ondr password=Iqh3h1223vdN1si8HrT413af';

        // }}}

        return $sys;
    }
}
