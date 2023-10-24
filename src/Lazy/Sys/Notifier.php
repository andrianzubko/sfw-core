<?php

namespace SFW\Lazy\Sys;

use PHPMailer\PHPMailer\{PHPMailer, Exception AS PHPMailerException};
use SFW\Exception\Logic;

/**
 * Notifier.
 */
class Notifier extends \SFW\Lazy\Sys
{
    /**
     * Default structure.
     */
    protected \SFW\NotifyStruct $defaultStruct;

    /**
     * Prepared notifies.
     */
    protected array $notifies = [];

    /**
     * Initializes default structure and registers shutdown process.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    public function __construct()
    {
        $this->defaultStruct = new \SFW\NotifyStruct();

        $this->defaultStruct->sender = self::$sys['config']['notifier_sender'];

        $this->defaultStruct->replies = self::$sys['config']['notifier_replies'];

        self::sys('Provider')->addListener(
            function (\SFW\Event\Shutdown $event) {
                register_shutdown_function($this->processAll(...));
            }
        );
    }

    /**
     * Adding notify to pool.
     */
    public function add(\SFW\Notify $notify): self
    {
        if (self::sys('Db')->isInTrans()) {
            self::sys('Provider')->addListener(
                function (\SFW\Event\TransactionCommitted $event) use ($notify) {
                    $this->notifies[] = $notify;
                }
            );
        } else {
            $this->notifies[] = $notify;
        }

        return $this;
    }

    /**
     * Call build() method at all notifies and send all messages.
     */
    protected function processAll(): void
    {
        while ($notify = array_shift($this->notifies)) {
            try {
                foreach ($notify->build(clone $this->defaultStruct) as $struct) {
                    try {
                        $this->send($struct);
                    } catch (\Throwable $e) {
                        self::sys('Logger')->error($e);
                    }
                }
            } catch (\Throwable $e) {
                self::sys('Logger')->error($e);
            }
        }
    }

    /**
     * Sending single message.
     *
     * @throws Logic
     * @throws PHPMailerException
     */
    protected function send(\SFW\NotifyStruct $struct): void
    {
        $mailer = new PHPMailer(true);

        $mailer->CharSet = 'utf-8';

        $mailer->Subject = $struct->subject;

        $mailer->msgHTML($struct->body);

        if (empty($struct->sender)) {
            throw new Logic('No sender in notify');
        }

        $mailer->setFrom(...(array) $struct->sender);

        if (self::$sys['config']['notifier_recipients'] !== null) {
            $struct->recipients = self::$sys['config']['notifier_recipients'];
        }

        if (empty($struct->recipients)) {
            throw new Logic('No recipients in notify');
        }

        foreach ($struct->recipients as $item) {
            try {
                $mailer->addAddress(...(array) $item);
            } catch (PHPMailerException) {
            }
        }

        foreach ($struct->replies as $item) {
            try {
                $mailer->addReplyTo(...(array) $item);
            } catch (PHPMailerException) {
            }
        }

        foreach ($struct->customHeaders as $item) {
            $mailer->addCustomHeader(...(array) $item);
        }

        foreach ($struct->attachmentFiles as $item) {
            $mailer->addAttachment(...(array) $item);
        }

        foreach ($struct->attachmentStrings as $item) {
            $mailer->addStringAttachment(...$item);
        }

        if (self::$sys['config']['notifier_enabled']) {
            try {
                $mailer->send();
            } catch (PHPMailerException) {
            }
        }
    }
}
