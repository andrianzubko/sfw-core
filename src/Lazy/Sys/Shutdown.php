<?php

namespace SFW\Lazy\Sys;

/**
 * Registers and unregisters shutdown callbacks.
 */
class Shutdown extends \SFW\Lazy\Sys
{
    /**
     * Registered callbacks.
     */
    protected array $callbacks;

    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Registers shutdown callback.
     */
    public function register(callable $callback, ?string $name = null): self
    {
        if (!isset($this->callbacks)) {
            register_shutdown_function(
                function () {
                    foreach ($this->callbacks as $callback) {
                        try {
                            $callback();
                        } catch (\Throwable $error) {
                            $this->sys('Logger')->error($error);
                        }
                    }
                }
            );
        }

        if (isset($name)) {
            $this->callbacks[$name] = $callback;
        } else {
            $this->callbacks[] = $callback;
        }

        return $this;
    }

    /**
     * Unregisters shutdown callback by name.
     */
    public function unregister(string $name): self
    {
        unset($this->callbacks[$name]);

        return $this;
    }

    /**
     * Unregisters all shutdown callbacks.
     */
    public function unregisterAll(): self
    {
        $this->callbacks = [];

        return $this;
    }
}
