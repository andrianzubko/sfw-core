<?php

namespace SFW;

use SFW\Exception\Runtime;

/**
 * Part of listeners provider.
 */
final class Provider extends \SFW\Base
{
    /**
     * Listener will be called many times.
     */
    public const REGULAR = 1;

    /**
     * Listener will only be called once.
     */
    public const DISPOSABLE = 2;

    /**
     * Listener will be called many times and can be removed only with 'force' parameter.
     */
    public const PERSISTENT = 3;

    /**
     * Internal cache.
     */
    protected array|false $cache;

    /**
     * Listener files.
     */
    protected array $lFiles;

    /**
     * Checks and actualize cache if needed.
     */
    public function __construct()
    {
        $this->cache = @include self::$sys['config']['provider_cache'];

        if ($this->cache === false || self::$sys['config']['env'] !== 'prod' && $this->isOutdated()) {
            $this->rebuild();
        }
    }

    /**
     * Gets file based listeners.
     *
     * @throws Runtime
     */
    public function getFileBasedListeners(): array
    {
        return $this->cache['listeners'];
    }

    /**
     * Scanning for listeners.
     */
    protected function scanForListenerFiles(): void
    {
        if (isset($this->lFiles)) {
            return;
        }

        $this->lFiles = [];

        foreach (self::sys('Dir')->scan(APP_DIR . '/src/Listener', true, true) as $item) {
            if (is_file($item) && str_ends_with($item, '.php')) {
                $this->lFiles[] = $item;
            }
        }
    }

    /**
     * Rechecks of the needs for cache rebuild.
     */
    protected function isOutdated(): bool
    {
        $this->scanForListenerFiles();

        if ($this->cache['count'] !== \count($this->lFiles)) {
            return true;
        }

        foreach ($this->lFiles as $file) {
            if ((int) filemtime($file) > $this->cache['time']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rescans listeners and rebuilds cache.
     *
     * @throws Runtime
     */
    protected function rebuild(): void
    {
        $this->scanForListenerFiles();

        foreach ($this->lFiles as $file) {
            require_once $file;
        }

        $this->cache = [];

        $this->cache['time'] = time();

        $this->cache['count'] = \count($this->lFiles);

        $this->cache['listeners'] = [];

        foreach (get_declared_classes() as $class) {
            if (str_starts_with($class, 'App\Listener\\')) {
                $rClass = new \ReflectionClass($class);

                foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                    foreach (
                        $rMethod->getAttributes(AsListener::class, \ReflectionAttribute::IS_INSTANCEOF)
                            as $attribute
                    ) {
                        if ($rMethod->isConstructor()) {
                            self::sys('Logger')->warning('Constructor cannot be a listener', options: [
                                'file' => $rMethod->getFileName(),
                                'line' => $rMethod->getStartLine()
                            ]);

                            continue;
                        }

                        $params = $rMethod->getParameters();

                        $type = $params ? $params[0]->getType() : null;

                        if ($type === null) {
                            self::sys('Logger')->warning('Listener must have one parameter with declared type', options: [
                                'file' => $rMethod->getFileName(),
                                'line' => $rMethod->getStartLine()
                            ]);

                            continue;
                        }

                        $instance = $attribute->newInstance();

                        $listener = [];

                        $listener['callback'] = [$class, $rMethod->name];

                        $listener['type'] = (string) $type;

                        $listener['mode'] = match ($instance::class) {
                            AsDisposableListener::class => self::DISPOSABLE,
                            AsPersistentListener::class => self::PERSISTENT,
                            default => self::REGULAR
                        };

                        $listener['priority'] = $instance->priority;

                        $this->cache['listeners'][] = $listener;
                    }
                }
            }
        }

        usort($this->cache['listeners'], fn($a, $b) => $a['priority'] <=> $b['priority']);

        foreach (array_keys($this->cache['listeners']) as $i) {
            unset($this->cache['listeners'][$i]['priority']);
        }

        if (!self::sys('File')->putVar(self::$sys['config']['provider_cache'], $this->cache, LOCK_EX)) {
            throw new Runtime(
                sprintf('Unable to write file %s', self::$sys['config']['provider_cache'])
            );
        }
    }
}
