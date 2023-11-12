<?php

declare(strict_types=1);

namespace SFW\Router;

/**
 * Listeners router.
 */
final class Listener extends \SFW\Router
{
    /**
     * Listener can be called only once.
     */
    public const DISPOSABLE = 'disposable';

    /**
     * Listener can be called many times.
     */
    public const REGULAR = 'regular';

    /**
     * Listener can be called many times and can only be removed with the force parameter.
     */
    public const PERSISTENT = 'persistent';

    /**
     * Internal cache.
     */
    protected static array|false $cache;

    /**
     * Reads and actualizes cache if needed.
     *
     * @throws \SFW\Exception\Runtime
     */
    public function __construct()
    {
        if (!isset(self::$cache)) {
            $this->readCache(self::$sys['config']['router_listeners_cache']);
        }
    }

    /**
     * Adds listener.
     *
     * @throws \SFW\Exception\InvalidArgument
     */
    public function add(callable $callback, string $mode): void
    {
        $params = (new \ReflectionFunction($callback(...)))->getParameters();

        $type = $params ? $params[0]->getType() : null;

        if ($type === null) {
            throw new \SFW\Exception\InvalidArgument('Listener must have first parameter with declared type');
        }

        $listener = [];

        $listener['callback'] = $callback;

        $listener['type'] = (string) $type;

        $listener['mode'] = $mode;

        self::$cache['listeners'][] = $listener;
    }

    /**
     * Removes listeners by event type.
     */
    public function removeByType(array|string $type, bool $force = false): void
    {
        foreach (self::$cache['listeners'] as $i => $listener) {
            if (($force || $listener['mode'] !== self::PERSISTENT)
                && \in_array($listener['type'], (array) $type, true)
            ) {
                unset(self::$cache['listeners'][$i]);
            }
        }
    }

    /**
     * Removes all listeners.
     */
    public function removeAll($force = false): void
    {
        if ($force) {
            self::$cache['listeners'] = [];
        } else {
            foreach (self::$cache['listeners'] as $i => $listener) {
                if ($listener['mode'] !== self::PERSISTENT) {
                    unset(self::$cache['listeners'][$i]);
                }
            }
        }
    }

    /**
     * Gets listeners for event.
     */
    public function getForEvent(object $event): iterable
    {
        foreach (self::$cache['listeners'] as $i => &$listener) {
            if ($event instanceof $listener['type']) {
                $listener['callback'] = \SFW\Utility::normalizeCallback($listener['callback']);

                if ($listener['mode'] === self::DISPOSABLE) {
                    unset(self::$cache['listeners'][$i]);
                }

                yield $listener['callback'];
            }
        }
    }

    /**
     * Rebuilds cache.
     */
    protected function rebuildCache(array $initialCache): void
    {
        self::$cache = $initialCache;

        self::$cache['listeners'] = [];

        foreach (get_declared_classes() as $class) {
            if (!str_starts_with($class, 'App\\')) {
                continue;
            }

            $rClass = new \ReflectionClass($class);

            foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach (
                    $rMethod->getAttributes(\SFW\AsSomeListener::class,
                        \ReflectionAttribute::IS_INSTANCEOF) as $rAttribute
                ) {
                    if ($rMethod->isConstructor()) {
                        self::sys('Logger')->warning("Constructor can't be a listener", options: [
                            'file' => $rMethod->getFileName(),
                            'line' => $rMethod->getStartLine(),
                        ]);

                        continue;
                    }

                    $params = $rMethod->getParameters();

                    $type = $params ? $params[0]->getType() : null;

                    if ($type === null) {
                        self::sys('Logger')->warning('Listener must have first parameter with declared type', options: [
                            'file' => $rMethod->getFileName(),
                            'line' => $rMethod->getStartLine(),
                        ]);

                        continue;
                    }

                    $instance = $rAttribute->newInstance();

                    $listener = [];

                    $listener['callback'] = "$class::$rMethod->name";

                    $listener['type'] = (string) $type;

                    $listener['mode'] = match ($instance::class) {
                        \SFW\AsPersistentListener::class => self::PERSISTENT,
                        \SFW\AsDisposableListener::class => self::DISPOSABLE,
                        default => self::REGULAR,
                    };

                    $listener['priority'] = $instance->priority;

                    self::$cache['listeners'][] = $listener;
                }
            }
        }

        usort(self::$cache['listeners'], fn($a, $b) => $a['priority'] <=> $b['priority']);

        foreach (array_keys(self::$cache['listeners']) as $i) {
            unset(self::$cache['listeners'][$i]['priority']);
        }
    }
}
