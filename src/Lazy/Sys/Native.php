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
        $text = $this->sys('Text');

        $templater = new \SFW\Templater\Native(
            [
                ...self::$config['sys']['templater']['native'],

                'debug' => self::$config['sys']['debug'],
            ]
        );

        $templater->addProperties(
            [
                'lc' => [$text, 'lc'],

                'lcFirst' => [$text, 'lcFirst'],

                'uc' => [$text, 'uc'],

                'ucFirst' => [$text, 'ucFirst'],

                'trim' => [$text, 'trim'],

                'rTrim' => [$text, 'rTrim'],

                'lTrim' => [$text, 'lTrim'],

                'fullTrim' => [$text, 'fullTrim'],

                'multiTrim' => [$text, 'multiTrim'],

                'cut' => [$text, 'cut'],

                'random' => [$text, 'random'],

                ...$this->properties,
            ]
        );

        return $templater;
    }
}
