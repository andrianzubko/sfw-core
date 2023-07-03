<?php

namespace SFW\Lazy\Sys;

/**
 * Default database.
 *
 * @mixin \SFW\Databaser\Driver
 */
class Db extends \SFW\Lazy\Sys
{
    /**
     * Reinstanting class if called with argument.
     */
    public function __construct(protected ?string $db = null) {}

    /**
     * Cache module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Databaser\Driver
    {
        return $this->sys($this->db ?? self::$config['sys']['db']);
    }
}
