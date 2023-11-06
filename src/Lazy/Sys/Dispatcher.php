<?php
declare(strict_types=1);

namespace SFW\Lazy\Sys;

use Psr\EventDispatcher\{EventDispatcherInterface, StoppableEventInterface};

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
    public function dispatch(object $event, bool $silent = false): object
    {
        foreach (self::sys('Provider')->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }

            if ($silent) {
                try {
                    $listener($event);
                } catch (\Throwable $e) {
                    self::sys('Logger')->error($e);
                }
            } else {
                $listener($event);
            }
        }

        return $event;
    }
}
