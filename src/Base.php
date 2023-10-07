<?php

namespace SFW;

/**
 * Abstraction with basic environment.
 */
#[\AllowDynamicProperties]
abstract class Base extends \stdClass
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
     * System parameters.
     */
    public static array $sys = [];

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
        if (class_exists("App\\Lazy\\Sys\\$name")) {
            return self::$sysLazies[$name] ??= "App\\Lazy\\Sys\\$name::getInstance"();
        }

        return self::$sysLazies[$name] ??= "SFW\\Lazy\\Sys\\$name::getInstance"();
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
     * Makes some important cleanups and exit.
     */
    public function exit(string|int $status = 0): void
    {
        unset(self::$sysLazies['Db']);

        foreach (self::$sysLazies as $lazy) {
            if ($lazy instanceof \SFW\Databaser\Driver
                && $lazy->isInTrans()
            ) {
                try {
                    $lazy->rollback();
                } catch (\SFW\Databaser\Exception) {
                }
            }
        }

        exit($status);
    }
}
