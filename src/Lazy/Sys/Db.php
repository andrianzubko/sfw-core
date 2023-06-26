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
    public function getInstance(): object
    {
        $db = $this->db ?? self::$config['db'];

        return self::$sys->{$db}();
    }
}
