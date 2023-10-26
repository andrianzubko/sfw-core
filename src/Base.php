<?php

namespace SFW;

/**
 * Abstraction for almost all framework classes.
 */
#[\AllowDynamicProperties]
abstract class Base extends \stdClass
{
    /**
     * System environment.
     */
    protected static array $sys = [];

    /**
     * Your environment.
     */
    protected static array $my = [];

    /**
     * Instances of system Lazy classes.
     */
    private static array $sysLazies = [];

    /**
     * Instances of your Lazy classes.
     */
    private static array $myLazies = [];

    /**
     * Accesses system Lazy classes from anywhere except templates.
     */
    final protected static function sys(string $name): object
    {
        return self::$sysLazies[$name] ??= class_exists("App\\Lazy\\Sys\\$name")
            ? "App\\Lazy\\Sys\\$name::getInstance"()
            : "SFW\\Lazy\\Sys\\$name::getInstance"();
    }

    /**
     * Accesses your Lazy classes from anywhere except templates.
     */
    final protected static function my(string $name): object
    {
        return self::$myLazies[$name] ??= "App\\Lazy\\My\\$name::getInstance"();
    }
}
