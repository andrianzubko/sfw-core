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
     * Initializing default struct and registering shutdown function.
     */
    public function __construct()
    {
        parent::__construct();

        $this->defaultStruct = new \SFW\NotifyStruct();

        $this->defaultStruct->e['config'] = self::$e['config'];

        $this->defaultStruct->e['sys'] = self::$e['sys'];

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
        $this->sys('Transaction')->onSuccess(
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
                $structs = $notify->build(clone $this->defaultStruct);
            } catch (\Throwable $error) {
                $this->sys('Logger')->error($error);

                continue;
            }

            if (self::$config['sys']['notifier']['enabled']) {
                foreach ($structs as $struct) {
                    if (isset(self::$config['sys']['notifier']['recipients'])) {
                        $struct->recipients = self::$config['sys']['notifier']['recipients'];
                    }

                    $this->send($struct);
                }
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
     */
    protected function send(\SFW\NotifyStruct $struct): void
    {
        try {
            $mailer = new PHPMailer(true);

            $mailer->CharSet = 'utf-8';

            $mailer->Subject = $struct->subject ?? '';

            $mailer->msgHTML($struct->body ?? '');

            $mailer->setFrom(...(array) (
                $struct->sender ?? self::$config['sys']['notifier']['sender']
            ));

            foreach ($struct->recipients ?? [] as $recipient) {
                try {
                    $mailer->addAddress(...(array) $recipient);
                } catch (PHPMailerException) {}
            }

            foreach ($struct->replies ?? self::$config['sys']['notifier']['replies'] as $reply) {
                try {
                    $mailer->addReplyTo(...(array) $reply);
                } catch (PHPMailerException) {}
            }

            foreach ($struct->customHeaders ?? [] as $header) {
                $mailer->addCustomHeader(...(array) $header);
            }

            foreach ($struct->attachmentsFiles ?? [] as $attachment) {
                $mailer->addAttachment(...(array) $attachment);
            }

            foreach ($struct->attachmentsStrings ?? [] as $attachment) {
                $mailer->addStringAttachment(...$attachment);
            }

            $mailer->send();
        } catch (PHPMailerException) {}
    }
}
