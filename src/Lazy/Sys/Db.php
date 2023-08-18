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
     * Reinstating class if called with argument.
     */
    public function __construct(protected ?string $db = null) {}

    /**
     * Databaser module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Databaser\Driver
    {
        return $this->sys($this->db ?? self::$config['sys']['db']['default']);
    }
}
