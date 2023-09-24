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
     * Makes URL by action (or full namespace) and optional parameters.
     *
     * @throws \SFW\RuntimeException
     */
    public function makeUrl(string $action, string ...$params): string
    {
        return \SFW\Router::makeUrl($action, ...$params);
    }
}
