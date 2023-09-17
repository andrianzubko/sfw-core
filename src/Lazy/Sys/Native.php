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
     * Additional properties for templates.
     */
    protected array $properties = [];

    /**
     * Native templater instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Templater\Processor
    {
        $templater = new \SFW\Templater\Native(
            [
                ...self::$config['sys']['templater']['native'],

                'debug' => self::$config['sys']['debug'],
            ]
        );

        $templater->addProperties(
            [
                'lc' => $this->sys('Text')->lc(...),

                'lcFirst' => $this->sys('Text')->lcFirst(...),

                'uc' => $this->sys('Text')->uc(...),

                'ucFirst' => $this->sys('Text')->ucFirst(...),

                'trim' => $this->sys('Text')->trim(...),

                'rTrim' => $this->sys('Text')->rTrim(...),

                'lTrim' => $this->sys('Text')->lTrim(...),

                'fullTrim' => $this->sys('Text')->fullTrim(...),

                'multiTrim' => $this->sys('Text')->multiTrim(...),

                'cut' => $this->sys('Text')->cut(...),

                'random' => $this->sys('Text')->random(...),

                ...$this->properties,
            ]
        );

        return $templater;
    }
}
