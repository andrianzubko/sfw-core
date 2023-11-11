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
     * Listeners router instance.
     */
    protected \SFW\Router\Listener $listenersRouter;

    /**
     * Instantiates the listeners' router.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    public function __construct()
    {
        $this->listenersRouter = new \SFW\Router\Listener();
    }

    /**
     * Adds disposable listener (can be called only once).
     *
     * @throws \SFW\Exception\InvalidArgument
     */
    public function addDisposableListener(callable $callback): self
    {
        $this->listenersRouter->add($callback, \SFW\Router\Listener::DISPOSABLE);

        return $this;
    }

    /**
     * Adds listener (can be called many times).
     *
     * @throws \SFW\Exception\InvalidArgument
     */
    public function addListener(callable $callback): self
    {
        $this->listenersRouter->add($callback, \SFW\Router\Listener::REGULAR);

        return $this;
    }

    /**
     * Adds persistent listener (can be called many times and can only be removed with the force parameter).
     *
     * @throws \SFW\Exception\InvalidArgument
     */
    public function addPersistentListener(callable $callback): self
    {
        $this->listenersRouter->add($callback, \SFW\Router\Listener::PERSISTENT);

        return $this;
    }

    /**
     * Removes listeners by event type.
     */
    public function removeListenersByType(array|string $type, bool $force = false): self
    {
        $this->listenersRouter->removeByType($type, $force);

        return $this;
    }

    /**
     * Removes all listeners.
     */
    public function removeAllListeners($force = false): self
    {
        $this->listenersRouter->removeAll($force);

        return $this;
    }

    /**
     * Gets listeners for event.
     */
    public function getListenersForEvent(object $event): iterable
    {
        yield from $this->listenersRouter->getForEvent($event);
    }
}
