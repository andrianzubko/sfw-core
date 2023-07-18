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
     * Timer of processed templates.
     */
    protected float $timer = 0;

    /**
     * Counter of processed templates.
     */
    protected int $counter = 0;

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
        $started = gettimeofday(true);

        $options['minify'] ??= self::$config['sys']['templater']['minify'];

        $options['debug'] ??= self::$config['sys']['debug'];

        $transformed = $this->templater->transform($e, APP_DIR . "/templates/$template", $options);

        $this->timer += gettimeofday(true) - $started;

        $this->counter += 1;

        return $transformed;
    }

    /**
     * Getting timer of processed templates.
     */
    public function getTimer(): float
    {
        return $this->timer;
    }

    /**
     * Getting count of processed templates.
     */
    public function getCounter(): int
    {
        return $this->counter;
    }
}
