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
        $this->options = self::$config['sys']['templater']['twig'];

        $this->options['reload'] = self::$config['sys']['env'] !== 'prod';

        $this->options['debug'] = self::$config['sys']['debug'];

        $this->options['globals'] = [
            'config' => self::$config['shared'],

            'sys' => self::$sys,
        ];

        $this->options['functions'] = [
            'lc' => $this->sys('Text')->lc(...),

            'lcFirst' => $this->sys('Text')->lcFirst(...),

            'uc' => $this->sys('Text')->uc(...),

            'ucFirst' => $this->sys('Text')->ucFirst(...),

            'trim' => $this->sys('Text')->trim(...),

            'rTrim' => $this->sys('Text')->rTrim(...),

            'lTrim' => $this->sys('Text')->lTrim(...),

            'fTrim' => $this->sys('Text')->fTrim(...),

            'mTrim' => $this->sys('Text')->mTrim(...),

            'cut' => $this->sys('Text')->cut(...),

            'random' => $this->sys('Text')->random(...),

            'makeUrl' => $this->sys('Router')->makeUrl(...),
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
