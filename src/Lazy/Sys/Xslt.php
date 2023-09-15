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
     * Properties for templates.
     */
    protected array $properties = [];

    /**
     * Xslt templater instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Templater\Processor
    {
        $templater = new \SFW\Templater\Xslt(self::$config['sys']['templater']['xslt']);

        $templater->addProperties($this->properties);

        return $templater;
    }
}
