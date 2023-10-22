<?php

namespace SFW;

use function array_key_exists;

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
     * Returns array with configuration parameters.
     */
    abstract public static function get(): array;

    /**
     * Gets parameter from server environment or env file.
     */
    protected static function env(string $key, mixed $default = null): mixed
    {
        if (!isset(self::$env)) {
            if (isset($_SERVER['APP_ENV'])) {
                self::$env = require APP_DIR . sprintf('/.env.%s.php', $_SERVER['APP_ENV']);
            } else {
                self::$env = require APP_DIR . '/.env.php';
            }
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        if (array_key_exists($key, self::$env)) {
            return self::$env[$key];
        }

        return $default;
    }
}
