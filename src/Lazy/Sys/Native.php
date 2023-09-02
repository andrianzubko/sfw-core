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
    protected array $properties;

    /**
     * Setting default properties.
     */
    public function __construct()
    {
        $text = $this->sys('Text');

        $this->properties = [
            'lc' => [$text, 'lc'],

            'lcfirst' => [$text, 'lcfirst'],

            'uc' => [$text, 'uc'],

            'ucfirst' => [$text, 'ucfirst'],

            'trim' => [$text, 'trim'],

            'rtrim' => [$text, 'rtrim'],

            'ltrim' => [$text, 'ltrim'],

            'fulltrim' => [$text, 'fulltrim'],

            'multitrim' => [$text, 'multitrim'],

            'cut' => [$text, 'cut'],

            'random' => [$text, 'random'],
        ];
    }

    /**
     * Native templater instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Templater\Processor
    {
        $templater = new \SFW\Templater\Native(
            array_merge(self::$config['sys']['templater']['native'],
                [
                    'debug' => self::$config['sys']['debug'],
                ]
            )
        );

        $templater->addProperties($this->properties);

        return $templater;
    }
}
