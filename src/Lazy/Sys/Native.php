<?php
declare(strict_types=1);

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
    protected array $options = [];

    /**
     * Initializes options for templates.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    protected function __construct()
    {
        $this->options['dir'] = self::$sys['config']['templater_native_dir'];

        $this->options['minify'] = self::$sys['config']['templater_native_minify'];

        $this->options['debug'] = self::$sys['config']['debug'];

        $this->options['properties']['sys'] = $this->filterConfig(self::$sys);

        $this->options['properties']['my'] = $this->filterConfig(self::$my);

        $this->options['properties']['lc'] = self::sys('Text')->lc(...);

        $this->options['properties']['lcFirst'] = self::sys('Text')->lcFirst(...);

        $this->options['properties']['uc'] = self::sys('Text')->uc(...);

        $this->options['properties']['ucFirst'] = self::sys('Text')->ucFirst(...);

        $this->options['properties']['trim'] = self::sys('Text')->trim(...);

        $this->options['properties']['rTrim'] = self::sys('Text')->rTrim(...);

        $this->options['properties']['lTrim'] = self::sys('Text')->lTrim(...);

        $this->options['properties']['fTrim'] = self::sys('Text')->fTrim(...);

        $this->options['properties']['mTrim'] = self::sys('Text')->mTrim(...);

        $this->options['properties']['cut'] = self::sys('Text')->cut(...);

        $this->options['properties']['random'] = self::sys('Text')->random(...);

        $this->options['properties']['genUrl'] = self::sys('Router')->genUrl(...);

        $this->options['properties']['genAbsoluteUrl'] = self::sys('Router')->genAbsoluteUrl(...);
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
