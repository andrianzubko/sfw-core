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
     * Makes URL by action (or full namespace) and optional parameters.
     *
     * @throws \SFW\RuntimeException
     */
    public function makeUrl(string $action, string|int|float|null ...$params): string
    {
        return $this->router->makeUrl($action, ...$params);
    }
}
