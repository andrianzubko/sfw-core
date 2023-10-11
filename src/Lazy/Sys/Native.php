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
        $this->options = self::$config['sys']['templater']['native'];

        $this->options['debug'] = self::$config['sys']['debug'];

        $this->options['properties'] = [
            'config' => self::$config['shared'],

            'sys' => self::$sys,

            'my' => self::$my,

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
