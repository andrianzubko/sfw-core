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
            $db = new \SFW\Databaser\Mysql(self::$config['sys']['db']['mysql'],
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
