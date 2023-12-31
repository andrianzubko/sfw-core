<?php

declare(strict_types=1);

namespace SFW\Lazy\Sys;

/**
 * Default databaser.
 *
 * @mixin \SFW\Databaser\Driver
 */
class Db extends \SFW\Lazy\Sys
{
    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct() {}

    /**
     * Databaser module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Databaser\Driver
    {
        return self::sys(self::$sys['config']['db']);
    }
}
