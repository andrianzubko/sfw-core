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
     * Templater module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Templater\Processor
    {
        return $this->sys(self::$config['sys']['templater']['default']);
    }
}
