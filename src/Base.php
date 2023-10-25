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
        if (!isset(self::$sysLazies[$name])) {
            if (class_exists("App\\Lazy\\Sys\\$name")) {
                self::$sysLazies[$name] = "App\\Lazy\\Sys\\$name::getInstance"();
            } else {
                self::$sysLazies[$name] = "SFW\\Lazy\\Sys\\$name::getInstance"();
            }
        }

        return self::$sysLazies[$name];
    }

    /**
     * Accesses your Lazy classes from anywhere except templates.
     */
    final protected static function my(string $name): object
    {
        if (!isset(self::$myLazies[$name])) {
            self::$myLazies[$name] = "App\\Lazy\\My\\$name::getInstance"();
        }

        return self::$myLazies[$name];
    }
}
