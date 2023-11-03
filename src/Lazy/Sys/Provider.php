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
     *
     * @throws Runtime
     */
    public function __construct()
    {
        $this->listeners = (new \SFW\Provider())->getFileBasedListeners();
    }

    /**
     * Adds listener.
     *
     * @throws InvalidArgument
     */
    public function addListener(callable $callback): self
    {
        return $this->addSomeListener($callback, \SFW\Provider::REGULAR);
    }

    /**
     * Adds disposable listener.
     *
     * @throws InvalidArgument
     */
    public function addDisposableListener(callable $callback): self
    {
        return $this->addSomeListener($callback, \SFW\Provider::DISPOSABLE);
    }

    /**
     * Adds persistent listener.
     *
     * @throws InvalidArgument
     */
    public function addPersistentListener(callable $callback): self
    {
        return $this->addSomeListener($callback, \SFW\Provider::PERSISTENT);
    }

    /**
     * Adds some listener.
     *
     * @throws InvalidArgument
     */
    protected function addSomeListener(callable $callback, int $mode): self
    {
        $params = (new \ReflectionFunction($callback(...)))->getParameters();

        $type = $params ? $params[0]->getType() : null;

        if ($type === null) {
            throw new InvalidArgument('Listener must have one parameter with declared type');
        }

        $this->listeners[] = [
            'callback' => $callback,
            'type' => (string) $type,
            'mode' => $mode,
        ];

        return $this;
    }

    /**
     * Removes listeners by event type.
     */
    public function removeListenersByType(array|string $type, bool $force = false): self
    {
        foreach ($this->listeners as $i => $listener) {
            if (($force || $listener['mode'] !== \SFW\Provider::PERSISTENT)
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
    public function removeAllListeners($force = false): self
    {
        if ($force) {
            $this->listeners = [];
        } else {
            foreach ($this->listeners as $i => $listener) {
                if ($listener['mode'] !== \SFW\Provider::PERSISTENT) {
                    unset($this->listeners[$i]);
                }
            }
        }

        return $this;
    }

    /**
     * Gets listeners for event.
     */
    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->listeners as $i => &$listener) {
            if ($event instanceof $listener['type']) {
                if (\is_array($listener['callback']) && \is_string($listener['callback'][0])) {
                    $listener['callback'][0] = $this->instances[$listener['callback'][0]]
                        ??= new $listener['callback'][0];
                }

                if ($listener['mode'] === \SFW\Provider::DISPOSABLE) {
                    unset($this->listeners[$i]);
                }

                yield $listener['callback'];
            }
        }
    }
}
