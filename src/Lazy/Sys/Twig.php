<?php
declare(strict_types=1);

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
    protected array $options = [];

    /**
     * Initializes options for templates.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    protected function __construct()
    {
        $this->options['dir'] = self::$sys['config']['templater_twig_dir'];

        $this->options['cache'] = self::$sys['config']['templater_twig_cache'];

        $this->options['strict'] = self::$sys['config']['templater_twig_strict'];

        $this->options['reload'] = self::$sys['config']['env'] !== 'prod';

        $this->options['debug'] = self::$sys['config']['debug'];

        $this->options['globals']['sys'] = $this->filterConfig(self::$sys);

        $this->options['globals']['my'] = $this->filterConfig(self::$my);

        $this->options['functions']['lc'] = self::sys('Text')->lc(...);

        $this->options['functions']['lcFirst'] = self::sys('Text')->lcFirst(...);

        $this->options['functions']['uc'] = self::sys('Text')->uc(...);

        $this->options['functions']['ucFirst'] = self::sys('Text')->ucFirst(...);

        $this->options['functions']['trim'] = self::sys('Text')->trim(...);

        $this->options['functions']['rTrim'] = self::sys('Text')->rTrim(...);

        $this->options['functions']['lTrim'] = self::sys('Text')->lTrim(...);

        $this->options['functions']['fTrim'] = self::sys('Text')->fTrim(...);

        $this->options['functions']['mTrim'] = self::sys('Text')->mTrim(...);

        $this->options['functions']['cut'] = self::sys('Text')->cut(...);

        $this->options['functions']['random'] = self::sys('Text')->random(...);

        $this->options['functions']['genUrl'] = self::sys('Router')->genUrl(...);

        $this->options['functions']['genAbsoluteUrl'] = self::sys('Router')->genAbsoluteUrl(...);
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
