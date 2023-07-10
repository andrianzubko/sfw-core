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
            $db = new \SFW\Databaser\Pgsql(self::$config['sys']['db']['pgsql'],
                [
                    $this->sys('Logger'), 'dbSlowQuery'
                ]
            );
        } catch (\SFW\Databaser\Exception $error) {
            $this->sys('Abend')->error($error->getMessage());
        }

        return $db;
    }
}
