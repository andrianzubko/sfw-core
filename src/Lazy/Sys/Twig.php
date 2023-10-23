<?php

namespace SFW\Lazy\Sys;

/**
 * Twig templater.
 *
 * @mixin \SFW\Templater\Processor
 */
class Twig extends \SFW\Lazy\Sys
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
            'dir' => self::$sys['config']['templater_twig_dir'],

            'cache' => self::$sys['config']['templater_twig_cache'],

            'strict' => self::$sys['config']['templater_twig_strict'],

            'reload' => self::$sys['config']['env'] !== 'prod',

            'debug' => self::$sys['config']['debug'],

            'globals' => [
                'sys' => $this->filterConfig(self::$sys),

                'my' => $this->filterConfig(self::$my),
            ],
            'functions' => [
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
     * Twig templater instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Templater\Processor
    {
        return new \SFW\Templater\Twig((new static())->options);
    }
}
