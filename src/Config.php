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

        return \array_key_exists($key, self::$env) ? self::$env[$key] : $default;
    }
}
