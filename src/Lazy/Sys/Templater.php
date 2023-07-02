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
                'lc' => [self::$sys->text(), 'lc'],

                'lcfirst' => [self::$sys->text(), 'lcfirst'],

                'uc' => [self::$sys->text(), 'uc'],

                'ucfirst' => [self::$sys->text(), 'ucfirst'],

                'trim' => [self::$sys->text(), 'trim'],

                'rtrim' => [self::$sys->text(), 'rtrim'],

                'ltrim' => [self::$sys->text(), 'ltrim'],

                'fulltrim' => [self::$sys->text(), 'fulltrim'],

                'multitrim' => [self::$sys->text(), 'multitrim'],

                'cut' => [self::$sys->text(), 'cut'],

                'random' => [self::$sys->text(), 'random'],
            ]
        );
    }

    /**
     * Transforming template to page.
     */
    public function transform(array $e, string $template): string
    {
        return $this->templater->transform($e, APP_DIR . "/templates/$template");
    }
}
