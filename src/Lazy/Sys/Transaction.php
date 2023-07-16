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
    protected array $onabort = [];

    /**
     * Do some action on transaction abort.
     */
    public function onabort(callable $event): void
    {
        $this->onabort[] = $event;
    }

    /**
     * Run transaction and die on unexpected errors.
     */
    public function run(
        ?string $isolation,
        ?array $expected,
        callable $transaction,
        ?callable $onerror = null
    ): bool {
        return $this->process($isolation, $expected, $transaction, $onerror, 'error');
    }

    /**
     * Run transaction and just warn on unexpected errors.
     */
    public function quiet(
        ?string $isolation,
        ?array $expected,
        callable $transaction,
        ?callable $onerror = null
    ): bool {
        return $this->process($isolation, $expected, $transaction, $onerror, 'warn');
    }

    /**
     * Processing transaction with retries on expected errors.
     */
    protected function process(
        ?string $isolation,
        ?array $expected,
        callable $transaction,
        ?callable $onerror,
        string $mode
    ): bool {
        for ($retry = 1; $retry <= self::$config['sys']['db']['transactions_retries']; $retry++) {
            try {
                $this->onabort = [];

                $this->sys('Db')->begin($isolation);

                if ($transaction() === false) {
                    $this->sys('Db')->rollback();

                    foreach ($this->onabort as $event) {
                        $event();
                    }
                } else {
                    $this->sys('Db')->commit();
                }

                return true;
            } catch (\SFW\Databaser\Exception $error) {
                $this->sys('Db')->rollback();

                foreach ($this->onabort as $event) {
                    $event();
                }

                if (isset($onerror)) {
                    $onerror($error->getSqlState());
                }

                $this->sys('Logger')->transactionFail($error->getSqlState(), $retry);

                if (!in_array($error->getSqlState(), $expected ?? [], true)
                    || $retry == self::$config['sys']['db']['transactions_retries']
                ) {
                    $this->sys('Abend')->$mode(
                        $error->getMessage(),
                        $error->getFile(),
                        $error->getLine()
                    );

                    return false;
                }
            }
        }

        return true;
    }
}
