<?php

namespace SFW\Lazy\Sys;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Events dispatcher.
 */
class Dispatcher extends \SFW\Lazy\Sys implements EventDispatcherInterface
{
    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Dispatches event.
     */
    public function dispatch(object $event): object
    {
        foreach (self::sys('Provider')->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }

            $listener($event);
        }

        return $event;
    }
}
