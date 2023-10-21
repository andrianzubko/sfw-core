<?php

namespace SFW\Lazy\Sys;

/**
 * Mysql.
 *
 * @mixin \SFW\Databaser\Driver
 */
class Mysql extends \SFW\Lazy\Sys
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
            'host' => self::$config['sys']['db_mysql_host'],

            'port' => self::$config['sys']['db_mysql_port'],

            'db' => self::$config['sys']['db_mysql_db'],

            'user' => self::$config['sys']['db_mysql_user'],

            'pass' => self::$config['sys']['db_mysql_pass'],

            'persistent' => self::$config['sys']['db_mysql_persistent'],

            'charset' => self::$config['sys']['db_mysql_charset'],

            'mode' => self::$config['sys']['db_mysql_mode'],

            'cleanup' => false,
        ];
    }

    /**
     * Mysql module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Databaser\Driver
    {
        return (new \SFW\Databaser\Mysql((new static())->options))
            ->setProfiler(self::sys('Logger')->dbSlowQuery(...));
    }
}
