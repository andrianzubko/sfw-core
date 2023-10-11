<?php

namespace SFW\Lazy\Sys;

/**
 * Part of router.
 */
class Router extends \SFW\Lazy\Sys
{
    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Generates URL by action (or full namespace) and optional parameters.
     *
     * @throws \SFW\Exception\Runtime
     */
    public function genUrl(string $action, string|int|float|null ...$params): string
    {
        return \SFW\Router\Controller::genUrl($action, ...$params);
    }

    /**
     * Generates absolute URL by action (or full namespace) and optional parameters.
     *
     * @throws \SFW\Exception\Runtime
     */
    public function genAbsoluteUrl(string $action, string|int|float|null ...$params): string
    {
        return self::$sys['url'] . \SFW\Router\Controller::genUrl($action, ...$params);
    }
}
