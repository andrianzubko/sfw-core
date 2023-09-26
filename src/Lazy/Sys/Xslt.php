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
     * Initializes properties for templates.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Xslt templater instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Templater\Processor
    {
        return
            (new \SFW\Templater\Xslt(self::$config['sys']['templater']['xslt']))
                ->addProperties(
                    (new static())->properties
                );
    }
}
