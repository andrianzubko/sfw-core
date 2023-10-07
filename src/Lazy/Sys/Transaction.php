<?php

namespace SFW\Lazy\Sys;

/**
 * Transaction.
 */
class Transaction extends \SFW\Lazy\Sys
{
    /**
     * Registered callbacks.
     */
    protected array $callbacks = [];

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

        for ($retry = 1; $retry <= self::$config['sys']['transaction']['retries']; $retry++) {
            try {
                $this->callbacks['success'] = [];

                $this->sys('Db')->begin($isolation);

                if ($body() !== false) {
                    $this->sys('Db')->commit();

                    foreach ($this->callbacks['success'] as $callback) {
                        $callback();
                    }
                } else {
                    $this->sys('Db')->rollback();
                }

                return $this->resetDriver();
            } catch (\SFW\Databaser\Exception $e) {
                try {
                    $this->sys('Db')->rollback();
                } catch (\SFW\Databaser\Exception) {
                }

                if (in_array($e->getSqlState(), $retryAt, true)
                    && $retry < self::$config['sys']['transaction']['retries']
                ) {
                    $this->sys('Logger')->logTransactionFail(
                        \Psr\Log\LogLevel::INFO, $e->getSqlState(), $retry
                    );
                } else {
                    $this->sys('Logger')->logTransactionFail(
                        \Psr\Log\LogLevel::ERROR, $e->getSqlState(), $retry
                    );

                    $this->resetDriver();

                    throw $e;
                }
            }
        }

        return $this->resetDriver();
    }

    /**
     * Sets driver.
     */
    private function setDriver(string $driver): void
    {
        self::$sysLazies['Db'] = $this->sys($driver);
    }

    /**
     * Resets driver to default.
     */
    private function resetDriver(): self
    {
        unset(self::$sysLazies['Db']);

        return $this;
    }

    /**
     * Do some action on successful commit of current transaction.
     *
     * If you call this method outside of transaction, then callback will be called immediately.
     */
    public function onSuccess(callable $callback): self
    {
        if ($this->sys('Db')->isInTrans()) {
            $this->callbacks['success'][] = $callback;
        } else {
            $callback();
        }

        return $this;
    }
}
