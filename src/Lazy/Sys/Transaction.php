<?php

namespace SFW\Lazy\Sys;

use Psr\Log\LogLevel;

/**
 * Transaction.
 */
class Transaction extends \SFW\Lazy\Sys
{
    /**
     * Registered callbacks for running on transaction abort.
     */
    protected array $onAbort = [];

    /**
     * Do some action on transaction abort.
     */
    public function onAbort(callable $event): void
    {
        $this->onAbort[] = $event;
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
    ): bool {
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
    ): bool {
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
        ?callable $onerror = null,
        string $driver = 'Db'
    ): bool {
        $this->setDriver($driver);

        for ($retry = 1; $retry <= self::$config['sys']['transaction']['retries']; $retry++) {
            try {
                $this->onAbort = [];

                $this->sys('Db')->begin($isolation);

                if ($body()) {
                    $this->sys('Db')->commit();
                } else {
                    $this->sys('Db')->rollback();

                    foreach ($this->onAbort as $event) {
                        $event();
                    }
                }

                $this->resetToDefaultDriver();

                return true;
            } catch (
                \SFW\Databaser\Exception $error
            ) {
                try {
                    $this->sys('Db')->rollback();
                } catch (\SFW\Databaser\Exception) {}

                foreach ($this->onAbort as $event) {
                    $event();
                }

                if (isset($onerror)) {
                    $onerror($error->getSqlState());
                }

                if (in_array($error->getSqlState(), $expected ?? [], true)
                    && $retry < self::$config['sys']['transaction']['retries']
                ) {
                    $this->sys('Logger')->logTransactionFail(
                        LogLevel::INFO, $error->getSqlState(), $retry
                    );
                } else {
                    $this->sys('Logger')->logTransactionFail(
                        LogLevel::ERROR, $error->getSqlState(), $retry
                    );

                    $this->resetToDefaultDriver();

                    throw $error;
                }
            }
        }

        $this->resetToDefaultDriver();

        return true;
    }

    /**
     * Sets database driver.
     */
    protected function setDriver(string $driver): void
    {
        self::$sysLazies['Db'] = $this->sys($driver);
    }

    /**
     * Resets database driver to default.
     */
    protected function resetToDefaultDriver(): void
    {
        self::$sysLazies['Db'] = $this->sys(self::$config['sys']['db']['default']);
    }
}
