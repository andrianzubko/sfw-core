<?php

namespace SFW\Lazy;

/**
 * Default database.
 */
class Db extends \SFW\Lazy
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

        return $this->{$db}();
    }
}
