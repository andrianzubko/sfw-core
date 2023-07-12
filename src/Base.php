<?php

namespace SFW;

/**
 * Abstraction with basic environment.
 */
#[\AllowDynamicProperties]
abstract class Base
{
    /**
     * Global microtime.
     */
    protected static float $globalMicrotime;

    /**
     * Instances of sys Lazy classes.
     */
    protected static array $sysLazyInstances = [];

    /**
     * Instances of your Lazy classes.
     */
    protected static array $myLazyInstances = [];

    /**
     * All configs (not available from templates).
     */
    protected static array $config = [];

    /**
     * Shared config, default and your environment (should be passed to templates).
     */
    protected static array $e = [];

    /**
     * Accessing system Lazy classes from anywhere except templates.
     *
     * $this->sys('SysLazyClass')->someMethod()
     */
    public function sys(string $name, ...$arguments): object
    {
        if (isset(self::$sysLazyInstances[$name])
            && !$arguments
        ) {
            return self::$sysLazyInstances[$name];
        }

        $class = "App\\Lazy\\Sys\\$name";

        if (!class_exists($class)) {
            $class = "SFW\\Lazy\\Sys\\$name";
        }

        $lazy = new $class(...$arguments);

        if (method_exists($lazy, 'getInstance')) {
            $lazy = $lazy->getInstance();
        }

        return self::$sysLazyInstances[$name] = $lazy;
    }

    /**
     * Accessing your Lazy classes from anywhere except templates.
     *
     * $this->my('MyLazyClass')->someMethod()
     */
    public function my(string $name, ...$arguments): object
    {
        if (isset(self::$myLazyInstances[$name])
            && !$arguments
        ) {
            return self::$myLazyInstances[$name];
        }

        $class = "App\\Lazy\\My\\$name";

        $lazy = new $class(...$arguments);

        if (method_exists($lazy, 'getInstance')) {
            $lazy = $lazy->getInstance();
        }

        return self::$myLazyInstances[$name] = $lazy;
    }
}
