<?php

namespace SFW;

/**
 * Basic abstract with basic environment.
 */
abstract class Base extends \stdClass
{
    /**
     * Global microtime.
     */
    protected static float $globalMicrotime;

    /**
     * Accessing system lazy classes from anywhere except templates.
     *
     * self::$sys->someClass()->someMethod()
     */
    public static Lazy\SysCaller $sys;

    /**
     * Accessing my (your) lazy classes from anywhere except templates.
     *
     * self::$my->someClass()->someMethod()
     */
    public static Lazy\MyCaller $my;

    /**
     * System configuration not available from templates.
     */
    protected static array $config;

    /**
     * Available from everywhere enviroment.
     */
    protected static array $e = [];
}
