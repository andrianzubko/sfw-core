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
        $text = $this->sys('Text');

        $this->templater = new \SFW\Templater();

        $this->templater->addProperties(
            [
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
            ]
        );
    }

    /**
     * Transforming template to page.
     */
    public function transform(array $e, string $template, array $options = []): string
    {
        $options['minify'] ??= self::$config['sys']['templater']['minify'];

        $options['debug'] ??= self::$config['sys']['debug'];

        return $this->templater->transform($e, APP_DIR . "/templates/$template", $options);
    }
}
