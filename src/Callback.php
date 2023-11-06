<?php

declare(strict_types=1);

namespace SFW;

/**
 * Utility class for working with callbacks.
 */
final class Callback
{
    /**
     * Instances.
     */
    private static array $instances = [];

    /**
     * Normalize callback.
     *
     * @throws Exception\InvalidArgument
     */
    public static function normalize(callable|array|string $callback): callable
    {
        if (is_callable($callback)) {
            return $callback;
        }

        try {
            if (\is_string($callback)) {
                $callback = explode('::', $callback, 2);

                if (\count($callback) === 2) {
                    $callback[0] = self::$instances[$callback[0]] ??= new $callback[0];
                } else {
                    $callback = $callback[0];
                }
            } elseif (\is_array($callback) && \is_string($callback[0])) {
                $callback[0] = self::$instances[$callback[0]] ??= new $callback[0];
            }
        } catch (\Throwable $e) {
            throw new Exception\InvalidArgument($e->getMessage());
        }

        if (is_callable($callback)) {
            return $callback;
        }

        throw new Exception\InvalidArgument('Unable to normalize callback');
    }
}
