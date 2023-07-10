<?php

namespace SFW\Lazy\Sys;

/**
 * Templater.
 */
class Templater extends \SFW\Lazy\Sys
{
    /**
     * Templater instance.
     */
    protected \SFW\Templater $templater;

    /**
     * Instantiating templater and adding some properties.
     */
    public function __construct()
    {
        $this->templater = new \SFW\Templater();

        $this->templater->addProperties(
            [
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
            ]
        );
    }

    /**
     * Transforming template to page.
     */
    public function transform(array $e, string $template, ?bool $minify = null): string
    {
        $template = APP_DIR . "/templates/$template";

        $minify ??= !self::$config['sys']['debug'];

        return $this->templater->transform($e, $template, $minify);
    }
}
