<?php

declare(strict_types=1);

namespace SFW\Lazy\Sys;

use SFW\Exception\Runtime;

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
     * Instantiate the router.
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
     * @throws Runtime
     */
    public function genUrl(string $action, string|int|float|null ...$params): string
    {
        return $this->router->genUrl($action, ...$params);
    }

    /**
     * Generates absolute URL by action (or full namespace) and optional parameters.
     *
     * @throws Runtime
     */
    public function genAbsoluteUrl(string $action, string|int|float|null ...$params): string
    {
        return self::$sys['url'] . $this->router->genUrl($action, ...$params);
    }
}
