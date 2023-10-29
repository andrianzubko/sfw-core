<?php

namespace SFW\Lazy\Sys;

use Psr\EventDispatcher\ListenerProviderInterface;
use SFW\Exception\{InvalidArgument, Runtime};

/**
 * Listeners provider.
 */
class Provider extends \SFW\Lazy\Sys implements ListenerProviderInterface
{
    /**
     * Listener will be called many times.
     */
    protected const REGULAR = 1;

    /**
     * Listener will only be called once.
     */
    protected const DISPOSABLE = 2;

    /**
     * Listener will be called many times and can't be removed.
     */
    protected const PERSISTENT = 3;

    /**
     * Listeners.
     */
    protected array $listeners;

    /**
     * Instances of same Listener classes.
     */
    protected array $instances = [];

    /**
     * Gets and actualize listeners if needed.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    public function __construct()
    {
        $this->listeners = $this->getFileBasedListeners();
    }

    /**
     * Adds regular listener.
     *
     * @throws InvalidArgument
     */
    public function addRegularListener(callable $callback): self
    {
        return $this->addListener($callback, self::REGULAR);
    }

    /**
     * Adds disposable listener.
     *
     * @throws InvalidArgument
     */
    public function addDisposableListener(callable $callback): self
    {
        return $this->addListener($callback, self::DISPOSABLE);
    }

    /**
     * Adds persistent listener.
     *
     * @throws InvalidArgument
     */
    public function addPersistentListener(callable $callback): self
    {
        return $this->addListener($callback, self::PERSISTENT);
    }

    /**
     * Adds listener base method.
     *
     * @throws InvalidArgument
     */
    protected function addListener(callable $callback, int $mode): self
    {
        $params = (new \ReflectionFunction($callback(...)))->getParameters();

        $type = $params ? $params[0]->getType() : null;

        if ($type === null) {
            throw new InvalidArgument('Listener must have one parameter with declared type');
        }

        $listener = [];

        $listener['callback'] = $callback;

        $listener['type'] = (string) $type;

        $listener['mode'] = $mode;

        $this->listeners[] = $listener;

        return $this;
    }

    /**
     * Removes listeners by event type.
     */
    public function removeListenersByType(array|string $type): self
    {
        foreach ($this->listeners as $i => $listener) {
            if ($listener['mode'] !== self::PERSISTENT
                && \in_array($listener['type'], (array) $type, true)
            ) {
                unset($this->listeners[$i]);
            }
        }

        return $this;
    }

    /**
     * Removes all listeners.
     */
    public function removeAllListeners(): self
    {
        $this->listeners = [];

        return $this;
    }

    /**
     * Gets listeners for event.
     */
    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->listeners as $i => &$listener) {
            if ($event instanceof $listener['type']) {
                if (\is_array($listener['callback'])
                    && \is_string($listener['callback'][0])
                ) {
                    $listener['callback'][0] = $this->instances[$listener['callback'][0]]
                        ??= new $listener['callback'][0];
                }

                if ($listener['mode'] === self::DISPOSABLE) {
                    unset($this->listeners[$i]);
                }

                yield $listener['callback'];
            }
        }
    }

    /**
     * Gets file based listeners.
     */
    protected function getFileBasedListeners(): array
    {
        // {{{ getting cache

        $cache = @include self::$sys['config']['provider_cache'];

        // }}}
        // {{{ quick return listeners in prod mode

        if ($cache !== false && self::$sys['config']['env'] === 'prod') {
            return $cache['listeners'];
        }

        // }}}
        // {{{ listeners files

        $lFiles = [];

        foreach (self::sys('Dir')->scan(APP_DIR . '/src/Listener', true, true) as $item) {
            if (is_file($item) && str_ends_with($item, '.php')) {
                $lFiles[] = $item;
            }
        }

        // }}}
        // {{{ checking cache for outdated

        if ($cache !== false) {
            if ($cache['count'] === \count($lFiles)) {
                foreach ($lFiles as $file) {
                    if ((int) filemtime($file) > $cache['time']) {
                        $cache = false;

                        break;
                    }
                }
            } else {
                $cache = false;
            }
        }

        if ($cache !== false) {
            return $cache['listeners'];
        }

        // }}}
        // {{{ loadings all listeners

        foreach ($lFiles as $file) {
            require_once $file;
        }

        // }}}
        // {{{ rebuilding cache

        $cache = [];

        $cache['time'] = time();

        $cache['count'] = \count($lFiles);

        $cache['listeners'] = [];

        foreach (get_declared_classes() as $class) {
            if (str_starts_with($class, 'App\Listener\\')) {
                $rClass = new \ReflectionClass($class);

                foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                    $attributes = $rMethod->getAttributes(\SFW\AsListener::class,
                        \ReflectionAttribute::IS_INSTANCEOF
                    );

                    foreach ($attributes as $attribute) {
                        $params = $rMethod->getParameters();

                        $type = $params ? $params[0]->getType() : null;

                        if ($type === null) {
                            self::sys('Logger')->warning('Listener must have one parameter with declared type', [
                                'file' => $rMethod->getFileName(),
                                'line' => $rMethod->getStartLine()
                            ]);
                        } else {
                            $instance = $attribute->newInstance();

                            $listener = [];

                            $listener['callback'] = [$class, $rMethod->name];

                            $listener['type'] = (string) $type;

                            $listener['mode'] = match ($instance::class) {
                                \SFW\AsDisposableListener::class => self::DISPOSABLE,
                                \SFW\AsPersistentListener::class => self::PERSISTENT,
                                default => self::REGULAR
                            };

                            $listener['priority'] = $instance->priority;

                            $cache['listeners'][] = $listener;
                        }
                    }
                }
            }
        }

        usort($cache['listeners'], fn($a,$b) => $a['priority'] <=> $b['priority']);

        foreach (array_keys($cache['listeners']) as $i) {
            unset($cache['listeners'][$i]['priority']);
        }

        // }}}
        // {{{ and saving

        if (!self::sys('File')->putVar(self::$sys['config']['provider_cache'], $cache, LOCK_EX)) {
            throw new Runtime(
                sprintf('Unable to write file %s', self::$sys['config']['provider_cache'])
            );
        }

        // }}}

        return $cache['listeners'];
    }
}
