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
     * Instances of system Lazy classes.
     */
    protected static array $sysLazies = [];

    /**
     * Instances of your Lazy classes.
     */
    protected static array $myLazies = [];

    /**
     * Accessing system Lazy classes from anywhere except templates.
     *
     * $this->sys('SomeSysLazyClass')->someMethod()
     */
    public function sys(string $name): object
    {
        if (!isset(self::$sysLazies[$name])) {
            $class = "App\\Lazy\\Sys\\$name";

            if (!class_exists($class)) {
                $class = "SFW\\Lazy\\Sys\\$name";
            }

            self::$sysLazies[$name] = (new $class())->getInstance();
        }

        return self::$sysLazies[$name];
    }

    /**
     * Accessing your Lazy classes from anywhere except templates.
     *
     * $this->my('SomeMyLazyClass')->someMethod()
     */
    public function my(string $name): object
    {
        if (!isset(self::$myLazies[$name])) {
            $class = "App\\Lazy\\My\\$name";

            self::$myLazies[$name] = (new $class())->getInstance();
        }

        return self::$myLazies[$name];
    }

    /**
     * Just exit with status.
     */
    public function end(string|int $status = 0): void
    {
        exit($status);
    }
}
