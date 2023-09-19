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
     * Adding notify to pool.
     */
    public function add(\SFW\Notify $notify): void
    {
        if (!isset($this->defaultStruct)) {
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

        $this->sys('Transaction')->onSuccess(
            function () use ($notify) {
                $this->notifies[] = $notify;
            }
        );
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
    public function removeAll(): void
    {
        $this->notifies = [];
    }

    /**
     * Sending single message.
     */
    protected function send(\SFW\NotifyStruct $struct): void
    {
        try {
            $mailer = new PHPMailer(true);

            $mailer->CharSet = 'utf-8';

            if (isset($struct->subject)) {
                $mailer->Subject = $struct->subject;
            }

            if (isset($struct->body)) {
                $mailer->msgHTML($struct->body);
            }

            if (is_array($struct->sender)) {
                $mailer->setFrom(...$struct->sender);
            } else {
                $mailer->setFrom($struct->sender);
            }

            foreach ($struct->recipients as $recipient) {
                try {
                    if (is_array($recipient)) {
                        $mailer->addAddress(...$recipient);
                    } else {
                        $mailer->addAddress($recipient);
                    }
                } catch (PHPMailerException) {}
            }

            foreach ($struct->replies as $reply) {
                try {
                    if (is_array($reply)) {
                        $mailer->addReplyTo(...$reply);
                    } else {
                        $mailer->addReplyTo($reply);
                    }
                } catch (PHPMailerException) {}
            }

            foreach ($struct->customHeaders as $header) {
                if (is_array($header)) {
                    $mailer->addCustomHeader(...$header);
                } else {
                    $mailer->addCustomHeader($header);
                }
            }

            foreach ($struct->attachmentsFiles as $attachment) {
                if (is_array($attachment)) {
                    $mailer->addAttachment(...$attachment);
                } else {
                    $mailer->addAttachment($attachment);
                }
            }

            foreach ($struct->attachmentsStrings as $attachment) {
                if (is_array($attachment)) {
                    $mailer->addStringAttachment(...$attachment);
                }
            }

            $mailer->send();
        } catch (PHPMailerException) {}
    }
}
