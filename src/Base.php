<?php

namespace SFW;

/**
 * Basic abstract with basic environment.
 */
abstract class Base extends \stdClass
{
    /**
     * Starting time.
     */
    protected static float $started;

    /**
     * System configuration not available from templates.
     */
    protected static array $config;

    /**
     * Available from everywhere default and user enviroment.
     */
    protected static array $e = [];

    /**
     * Already included lazy classes.
     */
    private static array $lazies = [];

    /**
     * This magic method allows you to access lazy classes from anywhere except templates.
     */
    final public function __call(string $name, array $arguments): object
    {
        if ( isset(self::$lazies[$name])) {
            return self::$lazies[$name];
        }

        $Name = ucfirst($name);

        $class = "App\\Lazy\\$Name";

        if (!class_exists($class)) {
            $class = "SFW\\Lazy\\$Name";
        }

        if ($arguments) {
            return new $class(...$arguments);
        }

        self::$lazies[$name] = (new $class())->getInstance();

        return self::$lazies[$name];
    }
}
