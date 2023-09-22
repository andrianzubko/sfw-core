<?php

namespace SFW;

/**
 * Router.
 */
abstract class Router extends Base
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
    public static function get(): array
    {
        return PHP_SAPI === 'cli'
            ? (new \SFW\Router\Command())->getRoute()
            : (new \SFW\Router\Controller())->getRoute();
    }

    /**
     * Makes URL by action (or full namespace) and optional parameters.
     *
     * @throws RuntimeException
     */
    public static function makeUrl(string $action, string|int|float ...$params): string
    {
        if (self::$cache === false) {
            (new \SFW\Router\Controller())->recheckCache();
        }

        $url = self::$cache['out'][$action]
            ?? self::$cache['out'][str_replace(['App\\Controller\\', '::__construct'], '', $action)]
            ?? '/';

        foreach ($params as $param) {
            $url = preg_replace('/{[^}]+}/', $param, $url, 1);
        }

        return $url;
    }

    /**
     * Gets full class name, method and action.
     */
    abstract protected function getRoute(): array;

    /**
     * Just check and rebuild cache if outdated.
     */
    protected function recheckCache(): void
    {
    }
}
