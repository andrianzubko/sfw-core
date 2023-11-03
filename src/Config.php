<?php

namespace SFW;

/**
 * Abstraction for Config classes.
 */
abstract class Config
{
    /**
     * Parameters from env file.
     */
    private static array $env;

    /**
     * If you need some of these parameters to be available in templates, list them in 'shared' parameter.
     */
    abstract public static function init(): array;

    /**
     * Gets parameter from env file.
     */
    protected static function env(string $key, mixed $default = null): mixed
    {
        if (!isset(self::$env)) {
            if (isset($_SERVER['APP_ENV'])) {
                $main = @include APP_DIR . "/.env.{$_SERVER['APP_ENV']}.php";

                $local = @include APP_DIR . "/.env.{$_SERVER['APP_ENV']}.local.php";
            } else {
                $main = @include APP_DIR . '/.env.php';

                $local = @include APP_DIR . '/.env.local.php';
            }

            if (!\is_array($main)) {
                $main = [];
            }

            if (!\is_array($local)) {
                $local = [];
            }

            self::$env = [...$main, ...$local];
        }

        return \array_key_exists($key, self::$env) ? self::$env[$key] : $default;
    }
}
