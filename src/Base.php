<?php

namespace SFW;

/**
 * Basic abstract with basic environment.
 */
abstract class Base extends \stdClass
{
    /**
     * Global microtime.
     */
    protected static float $globalMicrotime;

    /**
     * Instantiates of lazy classes.
     */
    protected static array $lazyInstances = [];

    /**
     * System configuration not available from templates.
     */
    protected static array $config;

    /**
     * Available from everywhere default and user enviroment.
     */
    protected static array $e = [];

    /**
     * This magic method allows you to access lazy classes from anywhere except templates.
     */
    final public function __call(string $name, array $arguments): object
    {
        if (!$arguments && isset(self::$lazyInstances[$name])) {
            return self::$lazyInstances[$name];
        }

        $class = 'App\\Lazy\\' . ucfirst($name);

        if (!class_exists($class)) {
            $class = 'SFW\\Lazy\\' . ucfirst($name);
        }

        $lazy = (new $class(...$arguments))->getInstance();

        self::$lazyInstances[$name] = $lazy;

        return $lazy;
    }
}
