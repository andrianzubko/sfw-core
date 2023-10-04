<?php

namespace SFW\Lazy\Sys;

use Psr\Log\LogLevel;

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
     * Processing pgsql transaction with retries on expected errors.
     *
     * @throws \SFW\Databaser\Exception
     */
    public function pgsql(
        callable $body,
        ?string $isolation = null,
        ?array $retryOn = null
    ): self {
        return $this->run($body, $isolation, $retryOn, 'Pgsql');
    }

    /**
     * Processing mysql transaction with retries on expected errors.
     *
     * @throws \SFW\Databaser\Exception
     */
    public function mysql(
        callable $body,
        ?string $isolation = null,
        ?array $retryOn = null
    ): self {
        return $this->run($body, $isolation, $retryOn, 'Mysql');
    }

    /**
     * Processing transaction with retries on expected errors.
     *
     * @throws \SFW\Databaser\Exception
     */
    public function run(
        callable $body,
        ?string $isolation = null,
        ?array $retryOn = null,
        string $driver = 'Db'
    ): self {
        self::$sysLazies['Db'] = $this->sys($driver);

        for ($retry = 1; $retry <= self::$config['sys']['transaction']['retries']; $retry++) {
            try {
                $this->callbacks = [];

                $this->sys('Db')->begin($isolation);

                if ($body()) {
                    $this->sys('Db')->commit();

                    if (isset($this->callbacks['success'])) {
                        foreach ($this->callbacks['success'] as $callback) {
                            $callback();
                        }
                    }
                } else {
                    $this->sys('Db')->rollback();
                }

                break;
            } catch (\SFW\Databaser\Exception $e) {
                try {
                    $this->sys('Db')->rollback();
                } catch (\SFW\Databaser\Exception) {
                }

                if (in_array($e->getSqlState(), $retryOn ?? [], true)
                    && $retry < self::$config['sys']['transaction']['retries']
                ) {
                    $this->sys('Logger')->logTransactionFail(
                        LogLevel::INFO,
                        $e->getSqlState(),
                        $retry
                    );

                    continue;
                }

                $this->sys('Logger')->logTransactionFail(
                    LogLevel::ERROR,
                    $e->getSqlState(),
                    $retry
                );

                self::$sysLazies['Db'] = $this->sys(self::$config['sys']['db']['default']);

                throw $e;
            }
        }

        self::$sysLazies['Db'] = $this->sys(self::$config['sys']['db']['default']);

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
