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
    protected static array $onAbort = [];

    /**
     * Used database driver.
     */
    protected string $db = 'Db';

    /**
     * Run transaction and throw on unexpected errors.
     *
     * @throws \SFW\Databaser\Exception
     */
    public function run(
        ?string $isolation,
        ?array $expected,
        callable $body,
        ?callable $onerror = null
    ): bool {
        return $this->process(__FUNCTION__, $isolation, $expected, $body, $onerror);
    }

    /**
     * Run transaction and just warn on unexpected errors.
     */
    public function quiet(
        ?string $isolation,
        ?array $expected,
        callable $body,
        ?callable $onerror = null
    ): bool {
        return $this->process(__FUNCTION__, $isolation, $expected, $body, $onerror);
    }

    /**
     * Processing transaction with retries on expected errors.
     *
     * @throws \SFW\Databaser\Exception
     */
    protected function process(
        string $caller,
        ?string $isolation,
        ?array $expected,
        callable $body,
        ?callable $onerror
    ): bool {
        $this->setDb();

        for ($retry = 1; $retry <= self::$config['sys']['transaction']['retries']; $retry++) {
            try {
                self::$onAbort = [];

                $this->sys('Db')->begin($isolation);

                if ($body()) {
                    $this->sys('Db')->commit();
                } else {
                    $this->sys('Db')->rollback();

                    foreach (self::$onAbort as $event) {
                        $event();
                    }
                }

                $this->resetToDefaultDb();

                return true;
            } catch (
                \SFW\Databaser\Exception $error
            ) {
                try {
                    $this->sys('Db')->rollback();
                } catch (\SFW\Databaser\Exception) {}

                foreach (self::$onAbort as $event) {
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

                    if ($caller === 'run') {
                        throw $error;
                    }

                    $this->sys('Logger')->error($error);

                    $this->resetToDefaultDb();

                    return false;
                }
            }
        }

        $this->resetToDefaultDb();

        return true;
    }

    /**
     * Sets database driver.
     */
    protected function setDb(): void
    {
        self::$sysLazyClasses['Db'] = $this->sys($this->db);
    }

    /**
     * Resets database driver to default.
     */
    protected function resetToDefaultDb(): void
    {
        self::$sysLazyClasses['Db'] = $this->sys(self::$config['sys']['db']['default']);
    }

    /**
     * Do some action on transaction abort.
     */
    public function onAbort(callable $event): void
    {
        self::$onAbort[] = $event;
    }

    /**
     * Sets some options.
     *
     * @throws \SFW\InvalidArgumentException
     *
     * @internal
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $option) {
            if ($option === 'Mysql' || $option === 'Pgsql') {
                $this->db = $option;
            } else {
                throw new \SFW\InvalidArgumentException("Unknown option $option");
            }
        }
    }
}
