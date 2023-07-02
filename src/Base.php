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
     * All configs (not available from templates).
     */
    protected static array $config = [];

    /**
     * Shared config, default and your environment (should be passed to templates).
     */
    protected static array $e = [];
}
