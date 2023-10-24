<?php

namespace SFW\Event;

/**
 * Emits after transaction is aborted on error.
 */
class TransactionAborted extends \SFW\Event
{
    /**
     * Passing sqlstate to property.
     */
    public function __construct(protected string $sqlState)
    {
    }

    /**
     * Gets sqlstate.
     */
    public function getSqlState(): string
    {
        return $this->sqlState;
    }
}
