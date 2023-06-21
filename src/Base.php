<?php

namespace SFW;

/**
 * Basic abstract with basic environment.
 */
abstract class Base extends \stdClass
{
    /**
     * Start microtime.
     */
    protected static float $startMicrotime;

    /**
     * Already included lazy classes.
     */
    private static array $lazyClasses = [];

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
        if ( isset(self::$lazyClasses[$name])) {
            return self::$lazyClasses[$name];
        }

        $class = 'App\\Lazy\\' . ucfirst($name);

        if (!class_exists($class)) {
            $class = 'SFW\\Lazy\\' . ucfirst($name);
        }

        $lazy = (new $class(...$arguments))->getInstance();

        if (!$arguments) {
            self::$lazyClasses[$name] = $lazy;
        }

        return $lazy;
    }
}
