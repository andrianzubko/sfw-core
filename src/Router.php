<?php

namespace SFW;

/**
 * Router.
 */
class Router extends Base
{
    /**
     * Gets target class name.
     *
     * @throws RuntimeException
     */
    public function get(): array
    {
        if (PHP_SAPI === 'cli') {
            $router = new \SFW\Router\Command();
        } else {
            $router = new \SFW\Router\Controller();
        }

        return $router->get();
    }
}
