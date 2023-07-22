<?php

namespace SFW;

/**
 * Exception handler.
 */
class Exception extends \Exception
{
    /**
     * Correct file and line.
     */
    public function __construct(string $message)
    {
        parent::__construct($message);

        foreach ($this->getTrace() as $trace) {
            if (!str_starts_with($trace['file'], dirname(__DIR__))) {
                $this->file = $trace['file'];

                $this->line = $trace['line'];

                break;
            }
        }
    }
}
