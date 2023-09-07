<?php

namespace SFW\Lazy\Sys;

/**
 * Default databaser.
 *
 * @mixin \SFW\Databaser\Driver
 */
class Db extends \SFW\Lazy\Sys
{
    /**
     * Databaser module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Databaser\Driver
    {
        return $this->sys(self::$config['sys']['db']['default']);
    }
}
