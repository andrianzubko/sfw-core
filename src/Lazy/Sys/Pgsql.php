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
     * Options for databaser.
     */
    protected array $options;

    /**
     * Initializes options for databaser.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    protected function __construct()
    {
        $this->options = [
            'host' => self::$config['sys']['db_pgsql_host'],

            'port' => self::$config['sys']['db_pgsql_port'],

            'db' => self::$config['sys']['db_pgsql_db'],

            'user' => self::$config['sys']['db_pgsql_user'],

            'pass' => self::$config['sys']['db_pgsql_pass'],

            'persistent' => self::$config['sys']['db_pgsql_persistent'],

            'charset' => self::$config['sys']['db_pgsql_charset'],

            'mode' => self::$config['sys']['db_pgsql_mode'],

            'cleanup' => false,
        ];
    }

    /**
     * Pgsql module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Databaser\Driver
    {
        return (new \SFW\Databaser\Pgsql((new static())->options))
            ->setProfiler(self::sys('Logger')->dbSlowQuery(...));
    }
}
