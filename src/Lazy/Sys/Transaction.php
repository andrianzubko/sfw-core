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
     * Run transaction and die on unexpected errors.
     */
    public function run(
        ?string $isolation,
        ?array $expected,
        callable $body,
        ?callable $onerror = null
    ): bool {
        return $this->process($isolation, $expected, $body, $onerror, 'error');
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
        return $this->process($isolation, $expected, $body, $onerror, 'warn');
    }

    /**
     * Processing transaction with retries on expected errors.
     */
    protected function process(
        ?string $isolation,
        ?array $expected,
        callable $body,
        ?callable $onerror,
        string $mode
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

                $this->sys('Logger')->transactionFail($error->getSqlState(), $retry);

                if (!in_array($error->getSqlState(), $expected ?? [], true)
                    || $retry == self::$config['sys']['transaction']['retries']
                ) {
                    $this->sys('Abend')->$mode($error);

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
     * @internal
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $option) {
            if ($option === 'Mysql' || $option === 'Pgsql') {
                $this->db = $option;
            } else {
                $this->sys('Abend')->error("Unknown option $option");
            }
        }
    }
}
