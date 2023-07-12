<?php

namespace SFW;

/**
 * Abstraction for all Config classes.
 */
abstract class Config
{
    /**
     * Parameters from env file.
     */
    private static array $env;

    /**
     * Returns array with config parameters.
     */
    abstract public static function get(): array;

    /**
     * Getting parameter from server environment or env file.
     */
    protected static function env(string $key, mixed $default = null): mixed
    {
        if (!isset(self::$env)) {
            $env = false;

            if (isset($_SERVER['APP_ENV'])) {
                $env = @include APP_DIR . "/.env.{$_SERVER['APP_ENV']}.php";
            }

            if ($env === false) {
                $env = @include APP_DIR . '/.env.php';
            }

            self::$env = is_array($env) ? $env : [];
        }

        return $_SERVER[$key] ?? self::$env[$key] ?? $default;
    }
}
