<?php

namespace SFW\Lazy\Sys;

use Psr\Log\LogLevel;

/**
 * Transaction.
 */
class Transaction extends \SFW\Lazy\Sys
{
    /**
     * Registered events callbacks.
     */
    protected array $events = [];

    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Processes Pgsql transaction with retries at expected errors.
     *
     * @throws \SFW\Databaser\Exception
     */
    public function pgsql(callable $body, ?string $isolation = null, array $retryAt = []): self
    {
        return $this->process('Pgsql', $body, $isolation, $retryAt);
    }

    /**
     * Processes Mysql transaction with retries at expected errors.
     *
     * @throws \SFW\Databaser\Exception
     */
    public function mysql(callable $body, ?string $isolation = null, array $retryAt = []): self
    {
        return $this->process('Mysql', $body, $isolation, $retryAt);
    }

    /**
     * Processes transaction with retries at expected errors.
     *
     * @throws \SFW\Databaser\Exception
     */
    public function run(callable $body, ?string $isolation = null, array $retryAt = []): self
    {
        return $this->process('Db', $body, $isolation, $retryAt);
    }

    /**
     * Base method for processing transaction.
     *
     * @throws \SFW\Databaser\Exception
     */
    protected function process(string $driver, callable $body, ?string $isolation, array $retryAt): self
    {
        $this->setDriver($driver);

        for ($retry = 1; $retry <= self::$config['sys']['transaction_retries']; $retry++) {
            try {
                $this->events['after_commit'] = [];

                self::sys('Db')->begin($isolation);

                if ($body() !== false) {
                    self::sys('Db')->commit();

                    foreach ($this->events['after_commit'] as $callback) {
                        $callback();
                    }
                } else {
                    self::sys('Db')->rollback();
                }

                return $this->resetDriver();
            } catch (\SFW\Databaser\Exception $e) {
                try {
                    self::sys('Db')->rollback();
                } catch (\SFW\Databaser\Exception) {
                }

                if (in_array($e->getSqlState(), $retryAt, true)
                    && $retry < self::$config['sys']['transaction_retries']
                ) {
                    self::sys('Logger')->transactionFail(LogLevel::INFO, $e->getSqlState(), $retry);
                } else {
                    self::sys('Logger')->transactionFail(LogLevel::ERROR, $e->getSqlState(), $retry);

                    $this->resetDriver();

                    throw $e;
                }
            }
        }

        return $this->resetDriver();
    }

    /**
     * Adds event listener.
     *
     * @throws \SFW\Exception\Logic
     */
    public function addListener(string $eventName, callable $callback): self
    {
        if ($eventName === 'after_commit') {
            if (self::sys('Db')->isInTrans()) {
                $this->events[$eventName][] = $callback;
            } else {
                $callback();
            }
        } else {
            throw new \SFW\Exception\Logic("Unknown event $eventName");
        }

        return $this;
    }

    /**
     * Sets driver.
     */
    private function setDriver(string $driver): void
    {
        self::$sysLazyInstances['Db'] = self::sys($driver);
    }

    /**
     * Resets driver to default.
     */
    private function resetDriver(): self
    {
        unset(self::$sysLazyInstances['Db']);

        return $this;
    }
}
