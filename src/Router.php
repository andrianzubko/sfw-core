<?php

namespace SFW;

/**
 * Router.
 */
class Router extends Base
{
    /**
     * Internal cache.
     */
    protected static array|false $cache = false;

    /**
     * Gets full class name, method and action.
     *
     * @throws RuntimeException
     */
    public function get(): array
    {
        if (PHP_SAPI === 'cli') {
            return (new \SFW\Router\Command())->get();
        }

        return (new \SFW\Router\Controller())->get();
    }

    /**
     * Makes URL by action (or full namespace) and optional parameters.
     *
     * @throws RuntimeException
     */
    public static function makeUrl(string $action, string ...$params): string
    {
        if (self::$cache === false) {
            (new \SFW\Router\Controller())->recheck();
        }

        $url = self::$cache['out'][$action]
            ?? self::$cache['out'][str_replace(['App\\Controller\\', '::__construct'], '', $action)]
            ?? '/';

        foreach ($params as $param) {
            $url = preg_replace('/{[^}]+}/', $param, $url, 1);
        }

        return $url;
    }
}
