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
     * Pgsql module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Databaser\Driver
    {
        return new \SFW\Databaser\Pgsql(self::$config['sys']['db']['pgsql'],
            profiler: [$this->sys('Logger'), 'dbSlowQuery']
        );
    }
}
