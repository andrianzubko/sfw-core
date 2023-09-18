<?php

namespace SFW\Lazy\Sys;

/**
 * Events handlers.
 */
class Event extends \SFW\Lazy\Sys
{
    /**
     * Wrapper for register_shutdown_function with try-catch and errors logging.
     */
    public function onShutdown(callable $callback, mixed ...$args): void
    {
        register_shutdown_function(
            function () use ($callback, $args): void {
                try {
                    $callback($args);
                } catch (\Throwable $error) {
                    $this->sys('Logger')->error($error);
                }
            }
        );
    }
}
