<?php

namespace SFW\Lazy\Sys;

/**
 * Default templater.
 *
 * @mixin \SFW\Templater\Processor
 */
class Templater extends \SFW\Lazy\Sys
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
     * Templater module instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Templater\Processor
    {
        return self::sys(self::$config['sys']['templater']['default']);
    }
}
