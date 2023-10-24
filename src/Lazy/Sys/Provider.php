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
     * Adds persistent listener.
     *
     * @throws InvalidArgument
     */
    public function addPersistentListener(\Closure $callback, ?string $tag = null): self
    {
        return $this->addListener($callback, $tag, true);
    }

    /**
     * Adds listener.
     *
     * @throws InvalidArgument
     */
    public function addListener(\Closure $callback, ?string $tag = null, bool $persistent = false): self
    {
        $params = (new \ReflectionFunction($callback))->getParameters();

        if (!$params || $params[0]->getType() === null) {
            throw new InvalidArgument(
                'Listener must have first parameter with declared object type they can accept'
            );
        }

        $listener = (object) [];

        $listener->callback = $callback;

        $listener->type = (string) $params[0]->getType();

        $listener->tag = $tag;

        $listener->persistent = $persistent;

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
     * Removes listeners by tag.
     */
    public function removeListenersByTag(array|string $tag): self
    {
        foreach ($this->listeners as $i => $listener) {
            if (\in_array($listener->tag, (array) $tag, true)) {
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
                if (!$listener->persistent) {
                    unset($this->listeners[$i]);
                }

                yield $listener->callback;
            }
        }
    }
}
