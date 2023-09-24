<?php

namespace SFW;

/**
 * Router.
 */
abstract class Router extends Base
{
    /**
     * Gets full class name, method and action.
     *
     * @throws RuntimeException
     */
    public static function get(): array
    {
        if (PHP_SAPI === 'cli') {
            $router = new \SFW\Router\Command();
        } else {
            $router = new \SFW\Router\Controller();
        }

        return $router->getAction();
    }

    /**
     * Gets full class name, method and action.
     */
    abstract protected function getAction(): array;
}
