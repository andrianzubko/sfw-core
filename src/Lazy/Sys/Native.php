<?php

namespace SFW\Lazy\Sys;

/**
 * Native templater.
 *
 * @mixin \SFW\Templater\Processor
 */
class Native extends \SFW\Lazy\Sys
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
            'dir' => self::$sys['config']['templater_native_dir'],

            'minify' => self::$sys['config']['templater_native_minify'],

            'debug' => self::$sys['config']['debug'],

            'properties' => [
                'sys' => $this->filterConfig(self::$sys),

                'my' => $this->filterConfig(self::$my),

                'lc' => self::sys('Text')->lc(...),

                'lcFirst' => self::sys('Text')->lcFirst(...),

                'uc' => self::sys('Text')->uc(...),

                'ucFirst' => self::sys('Text')->ucFirst(...),

                'trim' => self::sys('Text')->trim(...),

                'rTrim' => self::sys('Text')->rTrim(...),

                'lTrim' => self::sys('Text')->lTrim(...),

                'fTrim' => self::sys('Text')->fTrim(...),

                'mTrim' => self::sys('Text')->mTrim(...),

                'cut' => self::sys('Text')->cut(...),

                'random' => self::sys('Text')->random(...),

                'genUrl' => self::sys('Router')->genUrl(...),

                'genAbsoluteUrl' => self::sys('Router')->genAbsoluteUrl(...),
            ],
        ];
    }

    /**
     * Native templater instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Templater\Processor
    {
        return new \SFW\Templater\Native((new static())->options);
    }
}
