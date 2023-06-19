<?php

namespace SFW\Lazy;

/**
 * Transaction.
 */
class Transaction extends \SFW\Lazy
{
    /**
     * How much retries transaction with expected states.
     */
    protected int $retries = 7;

    /**
     * Registered callbacks for running on transaction abort.
     */
    protected array $onabort = [];

    /**
     * Do some action on transaction abort.
     */
    public function onabort(callable $event)
    {
        $this->onabort[] = $event;
    }

    /**
     * Run transaction and die on unexpected errors.
     */
    public function run(?string $isolation, ?array $expected, callable $transaction, ?callable $onerror = null): bool
    {
        return $this->process($isolation, $expected, $transaction, $onerror, 'error');
    }

    /**
     * Run transaction and just warn on unexpected errors.
     */
    public function quiet(?string $isolation, ?array $expected, callable $transaction, ?callable $onerror = null): bool
    {
        return $this->process($isolation, $expected, $transaction, $onerror, 'warn');
    }

    /**
     * Processing transaction with retries on expected errors.
     */
    protected function process(?string $isolation, ?array $expected, callable $transaction, ?callable $onerror, string $mode): bool
    {
        for ($retry = 1; $retry <= $this->retries; $retry++) {
            try {
                $this->onabort = [];

                $this->db()->begin($isolation);

                if ($transaction() === false) {
                    $this->db()->rollback();

                    foreach ($this->onabort as $event) {
                        $event();
                    }
                } else {
                    $this->db()->commit();
                }

                return true;
            } catch (\SFW\Databaser\Exception $error) {
                $this->db()->rollback();

                foreach ($this->onabort as $event) {
                    $event();
                }

                if (isset($onerror)) {
                    $onerror($error->getSqlState());
                }

                $logger = $this->getLogger();

                if ($logger) {
                    $logger($error->getSqlState(), $retry);
                }

                if (!in_array($error->getSqlState(), $expected ?? [], true) ||
                        $retry == $this->retries) {

                    $this->abend()->$mode($error->getMessage(), $error->getFile(), $error->getLine());

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Getting logger for transaction fails.
     */
    protected function getLogger(): ?\Closure
    {
        if (!isset(self::$config['dbTransactionsFailsLog'])) {
            return null;
        }

        return function (string $state, int $retry): void {
            $message = sprintf("[%s] [%d] %s",
                $state, $retry,
                    idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI']
            );

            $this->logger()->save(self::$config['dbTransactionsFailsLog'], $message);
        };
    }
}
