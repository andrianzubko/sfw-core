<?php

namespace SFW\Lazy;

/**
 * Caller for system lazy classes.
 */
final class SysCaller extends \SFW\Lazy
{
    /**
     * Already instantiated classes.
     */
    public static array $instances = [];

    /**
     * Access to lazy classes from anywhere except templates.
     */
    final public function __call(string $name, array $arguments): object
    {
        if (!$arguments && isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $class = 'App\\Lazy\\Sys\\' . ucfirst($name);

        if (!class_exists($class)) {
            $class = 'SFW\\Lazy\\Sys\\' . ucfirst($name);
        }

        $lazy = (new $class(...$arguments))->getInstance();

        self::$instances[$name] = $lazy;

        return $lazy;
    }
}
