<?php

namespace SFW\Lazy;

/**
 * Caller for your lazy classes.
 */
final class MyCaller extends \SFW\Lazy
{
    /**
     * Already instantiated classes.
     */
    public static array $instances = [];

    /**
     * Access to lazy classes from anywhere except templates.
     */
    public function __call(string $name, array $arguments): object
    {
        if (!$arguments && isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $class = 'App\\Lazy\\My\\' . ucfirst($name);

        $lazy = new $class(...$arguments);

        if (method_exists($lazy,'getInstance')) {
            $lazy = $lazy->getInstance();
        }

        return self::$instances[$name] = $lazy;
    }
}
