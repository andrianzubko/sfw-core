<?php

namespace SFW\Lazy\Sys;

/**
 * Pgsql.
 *
 * @mixin \SFW\Databaser\Driver
 */
class Pgsql extends \SFW\Lazy\Sys
{
    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Pgsql module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Databaser\Driver
    {
        $options = self::$config['sys']['db']['pgsql'];

        $options['cleanup'] = false;

        return (new \SFW\Databaser\Pgsql($options))->setProfiler(
            (new static())->sys('Logger')->logDbSlowQuery(...)
        );
    }
}
