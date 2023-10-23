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
     * Options for templates.
     */
    protected array $options;

    /**
     * Initializes options for templates.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    protected function __construct()
    {
        $this->options = [
            'dir' => self::$sys['config']['templater_xslt_dir'],

            'root' => self::$sys['config']['templater_xslt_root'],

            'item' => self::$sys['config']['templater_xslt_item'],

            'globals' => [
                'sys' => $this->filterConfig(self::$sys),

                'my' => $this->filterConfig(self::$my),
            ],
        ];
    }

    /**
     * Xslt templater instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Templater\Processor
    {
        return new \SFW\Templater\Xslt((new static())->options);
    }
}
