<?php
declare(strict_types=1);

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
                $primary = @include APP_DIR . "/.env.{$_SERVER['APP_ENV']}.php";

                $secondary = @include APP_DIR . "/.env.{$_SERVER['APP_ENV']}.local.php";
            } else {
                $primary = @include APP_DIR . '/.env.php';

                $secondary = @include APP_DIR . '/.env.local.php';
            }

            if (!\is_array($primary)) {
                $primary = [];
            }

            if (!\is_array($secondary)) {
                $secondary = [];
            }

            self::$env = [...$primary, ...$secondary];
        }

        return \array_key_exists($key, self::$env) ? self::$env[$key] : $default;
    }
}
