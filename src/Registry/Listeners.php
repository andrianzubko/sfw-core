<?php

declare(strict_types=1);

namespace SFW\Registry;

/**
 * Registry of listeners.
 */
final class Listeners extends \SFW\Registry
{
    /**
     * Checks and actualize cache if needed.
     *
     * @throws \SFW\Exception\Runtime
     */
    public function __construct()
    {
        parent::__construct(self::$sys['config']['listeners_cache']);
    }

    /**
     * Rebuilds cache.
     *
     * @throws \SFW\Exception\Runtime
     */
    protected function rebuildCache(): void
    {
        $this->cache = [];

        $this->cache['listeners'] = [];

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
                        \SFW\AsPersistentListener::class => 'persistent',
                        \SFW\AsDisposableListener::class => 'disposable',
                        default => 'regular',
                    };

                    $listener['priority'] = $instance->priority;

                    $this->cache['listeners'][] = $listener;
                }
            }
        }

        usort($this->cache['listeners'], fn($a, $b) => $a['priority'] <=> $b['priority']);

        foreach (array_keys($this->cache['listeners']) as $i) {
            unset($this->cache['listeners'][$i]['priority']);
        }
    }
}
