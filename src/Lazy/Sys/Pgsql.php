<?php

declare(strict_types=1);

namespace SFW\Lazy\Sys;

/**
 * Pgsql.
 *
 * @mixin \SFW\Databaser\Driver
 */
class Pgsql extends \SFW\Lazy\Sys
{
    /**
     * Options for databaser.
     */
    protected array $options = [];

    /**
     * Initializes options for databaser.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    protected function __construct()
    {
        $this->options['host'] = self::$sys['config']['db_pgsql_host'];

        $this->options['port'] = self::$sys['config']['db_pgsql_port'];

        $this->options['db'] = self::$sys['config']['db_pgsql_db'];

        $this->options['user'] = self::$sys['config']['db_pgsql_user'];

        $this->options['pass'] = self::$sys['config']['db_pgsql_pass'];

        $this->options['persistent'] = self::$sys['config']['db_pgsql_persistent'];

        $this->options['charset'] = self::$sys['config']['db_pgsql_charset'];

        $this->options['mode'] = self::$sys['config']['db_pgsql_mode'];

        $this->options['cleanup'] = false;
    }

    /**
     * Pgsql module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Databaser\Driver
    {
        $profiler = self::sys('Logger')->dbSlowQuery(...);

        return (new \SFW\Databaser\Pgsql((new static())->options))->setProfiler($profiler);
    }
}
