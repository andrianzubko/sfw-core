<?php

namespace SFW;

/**
 * Abstraction for almost all framework classes.
 */
#[\AllowDynamicProperties]
abstract class Base extends \stdClass
{
    /**
     * Started time.
     */
    protected static float $startedTime;

    /**
     * All configs (only shared are available from templates).
     */
    protected static array $config = [];

    /**
     * System environment (available from templates).
     */
    protected static array $sys = [];

    /**
     * Your environment (available from templates).
     */
    protected static array $my = [];

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
     * Accessing your Lazy classes from anywhere except templates.
     */
    final protected static function my(string $name): object
    {
        if (!isset(self::$myLazies[$name])) {
            self::$myLazies[$name] = "App\\Lazy\\My\\$name::getInstance"();
        }

        return self::$myLazies[$name];
    }

    /**
     * Makes some important cleanups and exit.
     */
    public function exit(string|int $status = 0): void
    {
        Utility::cleanup();

        exit($status);
    }
}
