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
     * Mysql module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Databaser\Driver
    {
        try {
            $db = new \SFW\Databaser\Mysql(
                [
                    'hostname' => self::$config['sys']['db_mysql_hostname'],
                    'username' => self::$config['sys']['db_mysql_username'],
                    'password' => self::$config['sys']['db_mysql_password'],
                    'database' => self::$config['sys']['db_mysql_database'],
                        'port' => self::$config['sys']['db_mysql_port'],
                      'socket' => self::$config['sys']['db_mysql_socket'],
                     'charset' => self::$config['sys']['db_mysql_charset'],
                ],[
                    $this->sys('Logger'), 'dbSlowQuery'
                ]
            );
        } catch (\SFW\Databaser\Exception $error) {
            $this->sys('Abend')->error($error->getMessage());
        }

        return $db;
    }
}
