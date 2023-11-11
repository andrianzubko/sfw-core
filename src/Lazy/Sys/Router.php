<?php

declare(strict_types=1);

namespace SFW\Lazy\Sys;

/**
 * Part of router.
 */
class Router extends \SFW\Lazy\Sys
{
    /**
     * Controllers router instance.
     */
    protected \SFW\Router\Controller $controllersRouter;

    /**
     * Instantiates the controllers' router.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    public function __construct()
    {
        $this->controllersRouter = new \SFW\Router\Controller();
    }

    /**
     * Generates URL by action and optional parameters.
     */
    public function genUrl(string $action, string|int|float|null ...$params): string
    {
        return $this->controllersRouter->genUrl($action, ...$params);
    }

    /**
     * Generates absolute URL by action and optional parameters.
     */
    public function genAbsoluteUrl(string $action, string|int|float|null ...$params): string
    {
        return self::$sys['url'] . $this->controllersRouter->genUrl($action, ...$params);
    }
}
