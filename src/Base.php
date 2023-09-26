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
        return self::$sysLazies[$name] ??=
            class_exists("App\\Lazy\\Sys\\$name")
                ? "App\\Lazy\\Sys\\$name::getInstance"()
                : "SFW\\Lazy\\Sys\\$name::getInstance"();
    }

    /**
     * Accessing your Lazy classes from anywhere except templates.
     *
     * $this->my('SomeMyLazyClass')->someMethod()
     */
    public function my(string $name): object
    {
        return self::$myLazies[$name] ??= "App\\Lazy\\My\\$name::getInstance"();
    }

    /**
     * Useful for fluent syntax exit.
     */
    public function exit(string|int $status = 0): void
    {
        exit($status);
    }
}
