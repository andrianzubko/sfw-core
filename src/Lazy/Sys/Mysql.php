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
        return (new \SFW\Databaser\Mysql(self::$config['sys']['db']['mysql']))
            ->setProfiler(
                $this->sys('Logger')->logDbSlowQuery(...)
            );
    }
}
