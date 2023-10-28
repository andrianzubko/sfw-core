<?php

namespace SFW\Lazy\Sys;

use Psr\Log\LogLevel;
use SFW\Event;

/**
 * Transaction.
 */
class Transaction extends \SFW\Lazy\Sys
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
        for ($retry = 1; $retry <= self::$sys['config']['transaction_retries']; $retry++) {
            self::sys('Provider')->removeListenersByType([
                Event\TransactionCommitted::class,
                Event\TransactionRolledBack::class
            ]);

            try {
                self::sys($driver)->begin($isolation);

                if ($body() !== false) {
                    self::sys($driver)->commit();

                    self::sys('Dispatcher')->dispatch(new Event\TransactionCommitted());
                } else {
                    self::sys($driver)->rollback();

                    self::sys('Dispatcher')->dispatch(new Event\TransactionRolledBack());
                }

                return $this;
            } catch (\SFW\Databaser\Exception $e) {
                try {
                    self::sys($driver)->rollback();
                } catch (\SFW\Databaser\Exception) {
                }

                if (\in_array($e->getSqlState(), $retryAt, true)
                    && $retry < self::$sys['config']['transaction_retries']
                ) {
                    self::sys('Logger')->transactionFail(LogLevel::INFO, $e->getSqlState(), $retry);
                } else {
                    self::sys('Logger')->transactionFail(LogLevel::ERROR, $e->getSqlState(), $retry);

                    throw $e;
                }
            }
        }

        return $this;
    }
}
