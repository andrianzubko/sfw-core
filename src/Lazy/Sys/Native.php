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
     * Setting default properties.
     */
    public function __construct()
    {
        $text = $this->sys('Text');

        foreach (
            [
                'lc',
                'lcfirst',
                'uc',
                'ucfirst',
                'trim',
                'rtrim',
                'ltrim',
                'fulltrim',
                'multitrim',
                'cut',
                'random',
            ] as $methodName
        ) {
            $this->properties[$methodName] = [$text, $methodName];
        }
    }

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

        $templater->addProperties($this->properties);

        return $templater;
    }
}
