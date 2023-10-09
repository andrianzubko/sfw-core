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
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Mysql module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Databaser\Driver
    {
        $options = self::$config['sys']['db']['mysql'];

        $options['cleanup'] = false;

        return (new \SFW\Databaser\Mysql($options))
            ->setProfiler((new static())->sys('Logger')->logDbSlowQuery(...));
    }
}
