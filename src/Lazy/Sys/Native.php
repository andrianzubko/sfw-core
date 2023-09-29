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
     * Native templater instance.
     *
     * @internal
     */
    public static function getInstance(): \SFW\Templater\Processor
    {
        return new \SFW\Templater\Native((new static())->options);
    }
}
