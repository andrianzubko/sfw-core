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
     * Properties for templates.
     */
    protected array $properties = [];

    /**
     * Initializes properties for templates.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    public function __construct()
    {
        $this->properties['lc'] = $this->sys('Text')->lc(...);

        $this->properties['lcFirst'] = $this->sys('Text')->lcFirst(...);

        $this->properties['uc'] = $this->sys('Text')->uc(...);

        $this->properties['ucFirst'] = $this->sys('Text')->ucFirst(...);

        $this->properties['trim'] = $this->sys('Text')->trim(...);

        $this->properties['rTrim'] = $this->sys('Text')->rTrim(...);

        $this->properties['lTrim'] = $this->sys('Text')->lTrim(...);

        $this->properties['fTrim'] = $this->sys('Text')->fTrim(...);

        $this->properties['mTrim'] = $this->sys('Text')->mTrim(...);

        $this->properties['cut'] = $this->sys('Text')->cut(...);

        $this->properties['random'] = $this->sys('Text')->random(...);

        $this->properties['makeUrl'] = \SFW\Router\Controller::makeUrl(...);
    }

    /**
     * Native templater instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Templater\Processor
    {
        return (new \SFW\Templater\Native(
                    self::$config['sys']['templater']['native'] + [
                        'debug' => self::$config['sys']['debug']
                    ]
                )
            )->addProperties($this->properties);
    }
}
