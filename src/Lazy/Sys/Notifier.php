<?php

namespace SFW\Lazy\Sys;

/**
 * Notifier.
 */
class Notifier extends \SFW\Lazy\Sys
{
    /**
     * Notifies queue.
     */
    protected array $notifies = [];

    /**
     * Registers shutdown sender.
     */
    public function __construct()
    {
        self::sys('Provider')->addPersistentListener(
            function (\SFW\Event\Shutdown $event) {
                register_shutdown_function($this->sendAll(...));
            }
        );
    }

    /**
     * Adds notify to queue only if current transaction successful commit, or it's called outside of transaction.
     */
    public function add(\SFW\Notify $notify): void
    {
        if (self::sys('Db')->isInTrans()) {
            self::sys('Provider')->addDisposableListener(
                function (\SFW\Event\TransactionCommitted $event) use ($notify) {
                    $this->notifies[] = $notify;
                }
            );
        } else {
            $this->notifies[] = $notify;
        }
    }

    /**
     * Calls send() method at all notifies and remove them from queue.
     */
    public function sendAll(): void
    {
        while ($this->notifies) {
            try {
                array_shift($this->notifies)->send();
            } catch (\Throwable $e) {
                self::sys('Logger')->error($e);
            }
        }
    }
}
