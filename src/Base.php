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
    protected static array $sysLazyInstances = [];

    /**
     * Instances of your Lazy classes.
     */
    protected static array $myLazyInstances = [];

    /**
     * Accesses system Lazy classes from anywhere except templates.
     */
    final protected static function sys(string $name): object
    {
        if (!isset(self::$sysLazyInstances[$name])) {
            if (class_exists("App\\Lazy\\Sys\\$name")) {
                self::$sysLazyInstances[$name] = "App\\Lazy\\Sys\\$name::getInstance"();
            } else {
                self::$sysLazyInstances[$name] = "SFW\\Lazy\\Sys\\$name::getInstance"();
            }
        }

        return self::$sysLazyInstances[$name];
    }

    /**
     * Accesses your Lazy classes from anywhere except templates.
     */
    final protected static function my(string $name): object
    {
        if (!isset(self::$myLazyInstances[$name])) {
            self::$myLazyInstances[$name] = "App\\Lazy\\My\\$name::getInstance"();
        }

        return self::$myLazyInstances[$name];
    }
}
