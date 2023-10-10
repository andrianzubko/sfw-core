<?php

namespace SFW\Lazy\Sys;

/**
 * Part of router.
 */
class Router extends \SFW\Lazy\Sys
{
    /**
     * Controllers router instance.
     */
    protected \SFW\Router\Controller $router;

    /**
     * Instantiates controllers router.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    public function __construct()
    {
        $this->router = new \SFW\Router\Controller();
    }

    /**
     * Generates URL by action (or full namespace) and optional parameters.
     *
     * @throws \SFW\Exception\Runtime
     */
    public function genUrl(string $action, string|int|float|null ...$params): string
    {
        return $this->router->genUrl($action, ...$params);
    }

    /**
     * Generates absolute URL by action (or full namespace) and optional parameters.
     *
     * @throws \SFW\Exception\Runtime
     */
    public function genAbsoluteUrl(string $action, string|int|float|null ...$params): string
    {
        return self::$sys['url'] . $this->router->genUrl($action, ...$params);
    }
}
