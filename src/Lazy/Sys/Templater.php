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
     * Reinstating class if called with argument.
     */
    public function __construct(protected ?string $templater = null) {}

    /**
     * Templater module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Templater\Processor
    {
        return $this->sys($this->templater ?? self::$config['sys']['templater']['default']);
    }
}
