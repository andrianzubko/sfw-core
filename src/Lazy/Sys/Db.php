<?php

namespace SFW\Lazy\Sys;

/**
 * Default database.
 */
class Db extends \SFW\Lazy\Sys
{
    /**
     * Reinstanting class if called with argument.
     */
    public function __construct(protected ?string $db = null) {}

    /**
     * Cache module instance.
     */
    public function getInstance(): \SFW\Databaser\Driver
    {
        $db = $this->db ?? self::$config['sys']['db'];

        return self::$sys->$db();
    }
}
