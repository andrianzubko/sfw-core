<?php

namespace SFW\Lazy;

/**
 * Templater.
 */
class Templater extends \App\Lazy
{
    /**
     * Add more properties to this array from constructor in nested class.
     */
    protected array $properties = [];

    /**
     * Transforming template to final page.
     */
    public function transform(array $e, string $template): string
    {
        return \SFW\Templater::transform("templates/$template",
            array_merge(
                [
                    'e' => $e,

                    'lc' => [$this->text(), 'lc'],

                    'lcfirst' => [$this->text(), 'lcfirst'],

                    'uc' => [$this->text(), 'uc'],

                    'ucfirst' => [$this->text(), 'ucfirst'],

                    'trim' => [$this->text(), 'trim'],

                    'rtrim' => [$this->text(), 'rtrim'],

                    'ltrim' => [$this->text(), 'ltrim'],

                    'fulltrim' => [$this->text(), 'fulltrim'],

                    'multitrim' => [$this->text(), 'multitrim'],

                    'cut' => [$this->text(), 'cut'],

                    'random' => [$this->text(), 'random'],

                ], $this->properties
            )
        );
    }
}
