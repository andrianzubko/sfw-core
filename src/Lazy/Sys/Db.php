<?php

namespace SFW\Lazy\Sys;

/**
 * Databaser.
 *
 * @mixin \SFW\Databaser
 */
class Db extends \SFW\Lazy\Sys
{
    /**
     * Database module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Databaser
    {
        return new \SFW\Databaser(
            self::$config['sys']['db']['dsn'],
            self::$config['sys']['db']['username'],
            self::$config['sys']['db']['password'],
            self::$config['sys']['db']['options'],

            profiler: [$this->sys('Logger'), 'dbSlowQuery']
        );
    }
}
