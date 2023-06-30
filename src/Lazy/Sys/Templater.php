<?php

namespace SFW\Lazy\Sys;

/**
 * Templater.
 */
class Templater extends \SFW\Lazy\Sys
{
    /**
     * More properties.
     */
    protected array $properties = [];

    /**
     * Transforming template to page.
     */
    public function transform(array $e, string $template): string
    {
        return \SFW\Templater::transform("templates/$template",
            array_merge(
                [
                    'e' => $e,

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

                ], $this->properties
            )
        );
    }
}
