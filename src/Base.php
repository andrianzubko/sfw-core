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
     * Instances of Lazy classes.
     */
    protected static array $lazyInstances = [];

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
        if (isset(self::$lazyInstances['sys'][$name])
            && !$arguments
        ) {
            return self::$lazyInstances['sys'][$name];
        }

        $class = "App\\Lazy\\Sys\\$name";

        if (!class_exists($class)) {
            $class = "SFW\\Lazy\\Sys\\$name";
        }

        $lazy = new $class(...$arguments);

        if (method_exists($lazy, 'getInstance')) {
            $lazy = $lazy->getInstance();
        }

        return self::$lazyInstances['sys'][$name] = $lazy;
    }

    /**
     * Accessing your Lazy classes from anywhere except templates.
     *
     * $this->my('MyLazyClass')->someMethod()
     */
    public function my(string $name, ...$arguments): object
    {
        if (isset(self::$lazyInstances['my'][$name])
            && !$arguments
        ) {
            return self::$lazyInstances['my'][$name];
        }

        $class = "App\\Lazy\\My\\$name";

        $lazy = new $class(...$arguments);

        if (method_exists($lazy, 'getInstance')) {
            $lazy = $lazy->getInstance();
        }

        return self::$lazyInstances['my'][$name] = $lazy;
    }
}
