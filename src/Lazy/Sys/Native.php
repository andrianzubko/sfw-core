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
        $this->properties = [
            'lc' => [$this->sys('Text'), 'lc'],

            'lcfirst' => [$this->sys('Text'), 'lcfirst'],

            'uc' => [$this->sys('Text'), 'uc'],

            'ucfirst' => [$this->sys('Text'), 'ucfirst'],

            'trim' => [$this->sys('Text'), 'trim'],

            'rtrim' => [$this->sys('Text'), 'rtrim'],

            'ltrim' => [$this->sys('Text'), 'ltrim'],

            'fulltrim' => [$this->sys('Text'), 'fulltrim'],

            'multitrim' => [$this->sys('Text'), 'multitrim'],

            'cut' => [$this->sys('Text'), 'cut'],

            'random' => [$this->sys('Text'), 'random'],
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
