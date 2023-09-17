<?php

namespace SFW;

/**
 * Abstraction for all Command classes.
 */
abstract class Command extends Base
{
    /**
     * Just exit with status.
     */
    public function end(string|int $status = 0): void
    {
        exit($status);
    }
}
