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
     * Accessing system Lazy classes from anywhere except templates.
     *
     * self::$sys->sysLazyClass()->someMethod()
     */
    public static Lazy\SysCaller $sys;

    /**
     * Accessing your Lazy classes from anywhere except templates.
     *
     * self::$my->myLazyClass()->someMethod()
     */
    public static Lazy\MyCaller $my;

    /**
     * Configs not available from templates.
     */
    protected static array $config = [];

    /**
     * Environment available from everywhere.
     */
    protected static array $e = [];
}
