<?php

namespace SFW\Lazy\Sys;

use Psr\EventDispatcher\ListenerProviderInterface;
use SFW\Exception\InvalidArgument;

/**
 * Listener provider.
 */
class Provider extends \SFW\Lazy\Sys implements ListenerProviderInterface
{
    /**
     * Listeners.
     */
    protected array $listeners = [];

    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Adds listener.
     *
     * @throws InvalidArgument
     */
    public function addListener(callable $callback): self
    {
        return $this->addSomeListener($callback, false);
    }

    /**
     * Adds disposable listener.
     *
     * @throws InvalidArgument
     */
    public function addDisposableListener(callable $callback): self
    {
        return $this->addSomeListener($callback, true);
    }

    /**
     * Adds listener base method.
     *
     * @throws InvalidArgument
     */
    protected function addSomeListener(callable $callback, bool $disposable): self
    {
        $params = (new \ReflectionFunction($callback(...)))->getParameters();

        $type = $params ? $params[0]->getType() : null;

        if ($type === null) {
            throw new InvalidArgument(
                'Listener must have first parameter with declared object type they can accept'
            );
        }

        $listener = (object) [];

        $listener->callback = $callback;

        $listener->type = (string) $type;

        $listener->disposable = $disposable;

        $this->listeners[] = $listener;

        return $this;
    }

    /**
     * Removes listeners by event type.
     */
    public function removeListenersByType(array|string $type): self
    {
        foreach ($this->listeners as $i => $listener) {
            if (\in_array($listener->type, (array) $type, true)) {
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
        foreach ($this->listeners as $i => $listener) {
            if ($event instanceof $listener->type) {
                if ($listener->disposable) {
                    unset($this->listeners[$i]);
                }

                yield $listener->callback;
            }
        }
    }
}
