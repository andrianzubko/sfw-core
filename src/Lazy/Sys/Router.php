<?php

namespace SFW\Lazy\Sys;

/**
 * Part of router.
 */
class Router extends \SFW\Lazy\Sys
{
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
