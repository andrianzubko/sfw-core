<?php

declare(strict_types=1);

namespace SFW;

/**
 * Registers command.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsCommand
{
    /**
     * Registers command.
     */
    public function __construct() {}
}
