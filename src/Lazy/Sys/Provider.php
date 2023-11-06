<?php

declare(strict_types=1);

namespace SFW\Lazy\Sys;

use Psr\EventDispatcher\ListenerProviderInterface;

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
     * Gets registered listeners.
     *
     * If your overrides constructor, don't forget call parent at first line!
     *
     * @throws \SFW\Exception\Runtime
     */
    public function __construct()
    {
        $this->listeners = (new \SFW\Registry\Listeners())->getCache()['listeners'];
    }

    /**
     * Adds listener.
     *
     * @throws \SFW\Exception\InvalidArgument
     */
    public function addListener(callable $callback): self
    {
        return $this->addSomeListener($callback, 'regular');
    }

    /**
     * Adds disposable listener.
     *
     * @throws \SFW\Exception\InvalidArgument
     */
    public function addDisposableListener(callable $callback): self
    {
        return $this->addSomeListener($callback, 'disposable');
    }

    /**
     * Adds persistent listener.
     *
     * @throws \SFW\Exception\InvalidArgument
     */
    public function addPersistentListener(callable $callback): self
    {
        return $this->addSomeListener($callback, 'persistent');
    }

    /**
     * Adds some listener.
     *
     * @throws \SFW\Exception\InvalidArgument
     */
    protected function addSomeListener(callable $callback, string $mode): self
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

        $this->listeners[] = $listener;

        return $this;
    }

    /**
     * Removes listeners by event type.
     */
    public function removeListenersByType(array|string $type, bool $force = false): self
    {
        foreach ($this->listeners as $i => $listener) {
            if (($force || $listener['mode'] !== 'persistent')
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
                if ($listener['mode'] !== 'persistent') {
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
                $listener['callback'] = \SFW\Callback::normalize($listener['callback']);

                if ($listener['mode'] === 'disposable') {
                    unset($this->listeners[$i]);
                }

                yield $listener['callback'];
            }
        }
    }
}
