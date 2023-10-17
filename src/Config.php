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
     * Returns array with config parameters.
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

        return $_SERVER[$key] ?? self::$env[$key] ?? $default;
    }
}
