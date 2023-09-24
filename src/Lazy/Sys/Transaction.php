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
        ?string $isolation,
        ?array $expected,
        callable $body,
        ?callable $onerror = null
    ): self {
        return $this->run($isolation, $expected, $body, $onerror, 'Pgsql');
    }

    /**
     * Processing mysql transaction with retries on expected errors.
     *
     * @throws \SFW\Databaser\Exception
     */
    public function mysql(
        ?string $isolation,
        ?array $expected,
        callable $body,
        ?callable $onerror = null
    ): self {
        return $this->run($isolation, $expected, $body, $onerror, 'Mysql');
    }

    /**
     * Processing transaction with retries on expected errors.
     *
     * @throws \SFW\Databaser\Exception
     */
    public function run(
        ?string $isolation,
        ?array $expected,
        callable $body,
        ?callable $onAbort = null,
        string $driver = 'Db'
    ): self {
        self::$sysLazies['Db'] = $this->sys($driver);

        for ($retry = 1; $retry <= self::$config['sys']['transaction']['retries']; $retry++) {
            try {
                $this->callbacks['success'] = [];

                $this->sys('Db')->begin($isolation);

                if ($body()) {
                    $this->sys('Db')->commit();

                    foreach ($this->callbacks['success'] as $callback) {
                        $callback();
                    }
                } else {
                    $this->sys('Db')->rollback();
                }

                break;
            } catch (
                \SFW\Databaser\Exception $error
            ) {
                try {
                    $this->sys('Db')->rollback();
                } catch (\SFW\Databaser\Exception) {}

                if (isset($onAbort)) {
                    $onAbort($error->getSqlState());
                }

                if (in_array($error->getSqlState(), $expected ?? [], true)
                    && $retry < self::$config['sys']['transaction']['retries']
                ) {
                    $this->sys('Logger')->logTransactionFail(
                        LogLevel::INFO,
                        $error->getSqlState(),
                        $retry
                    );

                    continue;
                }

                $this->sys('Logger')->logTransactionFail(
                    LogLevel::ERROR,
                    $error->getSqlState(),
                    $retry
                );

                self::$sysLazies['Db'] = $this->sys(self::$config['sys']['db']['default']);

                throw $error;
            }
        }

        self::$sysLazies['Db'] = $this->sys(self::$config['sys']['db']['default']);

        return $this;
    }

    /**
     * Do some action on successful commit.
     *
     * If there is no active transaction, then callback will be called immediately.
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
