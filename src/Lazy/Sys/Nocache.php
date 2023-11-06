<?php

declare(strict_types=1);

namespace SFW\Lazy\Sys;

/**
 * Nocache.
 *
 * @mixin \SFW\Cacher\Driver
 */
class Nocache extends \SFW\Lazy\Sys
{
    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct() {}

    /**
     * Nocache module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Cacher\Driver
    {
        return new \SFW\Cacher\Nocache();
    }
}
