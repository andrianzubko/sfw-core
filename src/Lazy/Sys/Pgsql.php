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
        try {
            $db = new \SFW\Databaser\Pgsql(
                [
                    'connection' => self::$config['sys']['db_pgsql_connection'],
                      'encoding' => self::$config['sys']['db_pgsql_encoding'],
                    'persistent' => self::$config['sys']['db_pgsql_persistent'],
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
