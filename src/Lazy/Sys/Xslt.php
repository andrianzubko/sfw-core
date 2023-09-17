<?php

namespace SFW\Lazy\Sys;

/**
 * Xslt templater.
 *
 * @mixin \SFW\Templater\Processor
 */
class Xslt extends \SFW\Lazy\Sys
{
    /**
     * Additional properties for templates.
     */
    protected array $properties = [];

    /**
     * Xslt templater instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Templater\Processor
    {
        return (new \SFW\Templater\Xslt(self::$config['sys']['templater']['xslt']))
            ->addProperties(
                $this->properties
            );
    }
}
