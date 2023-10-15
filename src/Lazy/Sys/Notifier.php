<?php

namespace SFW\Lazy\Sys;

use PHPMailer\PHPMailer\{PHPMailer, Exception AS PHPMailerException};

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

        $this->defaultStruct->sender = self::$config['sys']['notifier']['sender'];

        $this->defaultStruct->replies = self::$config['sys']['notifier']['replies'];

        register_shutdown_function(
            function () {
                register_shutdown_function(
                    $this->processAll(...)
                );
            }
        );
    }

    /**
     * Adding notify to pool.
     */
    public function add(\SFW\Notify $notify): self
    {
        self::sys('Transaction')->addListener('after_commit',
            function () use ($notify) {
                $this->notifies[] = $notify;
            }
        );

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
     * Remove all notifies from queue.
     */
    public function removeAll(): self
    {
        $this->notifies = [];

        return $this;
    }

    /**
     * Sending single message.
     *
     * @throws \SFW\Exception\Logic
     * @throws PHPMailerException
     */
    protected function send(\SFW\NotifyStruct $struct): void
    {
        $mailer = new PHPMailer(true);

        $mailer->CharSet = 'utf-8';

        $mailer->Subject = $struct->subject;

        $mailer->msgHTML($struct->body);

        if (empty($struct->sender)) {
            throw new \SFW\Exception\Logic('No sender in notify');
        }

        $mailer->setFrom(...(array) $struct->sender);

        if (isset(self::$config['sys']['notifier']['recipients'])) {
            $struct->recipients = self::$config['sys']['notifier']['recipients'];
        }

        if (empty($struct->recipients)) {
            throw new \SFW\Exception\Logic('No recipients in notify');
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

        if (self::$config['sys']['notifier']['enabled']) {
            try {
                $mailer->send();
            } catch (PHPMailerException) {
            }
        }
    }
}
