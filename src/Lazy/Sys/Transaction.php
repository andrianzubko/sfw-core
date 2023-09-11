<?php

namespace SFW\Lazy\Sys;

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
     * Used database driver.
     */
    protected string $db = 'Db';

    /**
     * Run transaction and throw on unexpected errors.
     *
     * @throws \SFW\RuntimeException
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
     * @throws \SFW\RuntimeException
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

                $this->resetToDefaultDb();

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

                $isExpected = in_array($error->getSqlState(), $expected ?? [], true);

                $this->sys('Logger')->logTransactionFail(
                    $isExpected,
                    $error->getSqlState(),
                    $retry
                );

                if (!$isExpected
                    || $retry == self::$config['sys']['transaction']['retries']
                ) {
                    if ($caller === 'run') {
                        throw new \SFW\RuntimeException($error->getMessage());
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
        $this->onAbort[] = $event;
    }

    /**
     * Sets some options.
     *
     * @throws \SFW\RuntimeException
     *
     * @internal
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $option) {
            if ($option === 'Mysql' || $option === 'Pgsql') {
                $this->db = $option;
            } else {
                throw new \SFW\RuntimeException("Unknown option $option");
            }
        }
    }
}
