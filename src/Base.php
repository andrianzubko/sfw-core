<?php

namespace SFW;

/**
 * Abstraction with basic environment.
 */
#[\AllowDynamicProperties]
abstract class Base
{
    /**
     * Started time.
     */
    public static float $startedTime;

    /**
     * All configs (not available from templates).
     */
    public static array $config = [];

    /**
     * Shared config, default and your environment (should be passed to templates).
     */
    public static array $e = [];

    /**
     * Instances of sys Lazy classes.
     */
    protected static array $sysLazyClasses = [];

    /**
     * Instances of your Lazy classes.
     */
    protected static array $myLazyClasses = [];

    /**
     * Accessing system Lazy classes from anywhere except templates.
     *
     * $this->sys('SysLazyClass')->someMethod()
     */
    public function sys(string $name): object
    {
        if ( isset(self::$sysLazyClasses[$name])) {
            return self::$sysLazyClasses[$name];
        }

        $options = explode(':', $name);

        $primaryName = array_shift($options);

        $class = "App\\Lazy\\Sys\\$primaryName";

        if (!class_exists($class)) {
            $class = "SFW\\Lazy\\Sys\\$primaryName";
        }

        $lazy = new $class();

        if (method_exists($lazy, 'getInstance')) {
            $lazy = $lazy->getInstance();
        } elseif ($options) {
            if (method_exists($lazy, 'setOptions')) {
                $lazy->setOptions($options);
            }

            self::$sysLazyClasses[$primaryName] ??= new $class();
        }

        return self::$sysLazyClasses[$name] = $lazy;
    }

    /**
     * Accessing your Lazy classes from anywhere except templates.
     *
     * $this->my('MyLazyClass')->someMethod()
     */
    public function my(string $name): object
    {
        if ( isset(self::$myLazyClasses[$name])) {
            return self::$myLazyClasses[$name];
        }

        $class = "App\\Lazy\\My\\$name";

        $lazy = new $class();

        if (method_exists($lazy, 'getInstance')) {
            $lazy = $lazy->getInstance();
        }

        return self::$myLazyClasses[$name] = $lazy;
    }
}
