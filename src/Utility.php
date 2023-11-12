<?php

declare(strict_types=1);

namespace SFW;

/**
 * Utility class.
 */
class Utility
{
    /**
     * Normalize callback.
     *
     * @throws Exception\Runtime
     */
    public static function normalizeCallback(callable|array|string $callback): callable
    {
        if (is_callable($callback)) {
            return $callback;
        }

        if (\is_string($callback)) {
            $callback = explode('::', $callback);
        }

        static $instances = [];

        try {
            $callback[0] = $instances[$callback[0]] ??= new $callback[0];
        } catch (\Throwable $e) {
            throw new Exception\Runtime($e->getMessage());
        }

        if (is_callable($callback)) {
            return $callback;
        }

        throw new Exception\Runtime('Unable to normalize callback');
    }
}
