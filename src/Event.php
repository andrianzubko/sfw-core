<?php

declare(strict_types=1);

namespace SFW;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Abstraction for Event classes.
 */
abstract class Event extends Base implements StoppableEventInterface
{
    /**
     * Propagation stopped flag.
     */
    private bool $propagationStopped = false;

    /**
     * Is propagation stopped?
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     */
    public function stopPropagation(): self
    {
        $this->propagationStopped = true;

        return $this;
    }
}
