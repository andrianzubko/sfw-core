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
     * Just in case of adding properties.
     */
    public function __construct() {}

    /**
     * Transforming template to page.
     */
    public function transform(array $e, string $template): string
    {
        $text = self::$sys->text();

        return \SFW\Templater::transform("templates/$template",
            array_merge(
                [
                    'e' => $e,

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

                ], $this->properties
            )
        );
    }
}
