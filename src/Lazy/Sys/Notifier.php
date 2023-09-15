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
    protected static \SFW\NotifyStruct $defaultStruct;

    /**
     * Prepared notifies.
     */
    protected static array $notifies = [];

    /**
     * Adding notify to pool.
     */
    public function add(\SFW\Notify $notify): void
    {
        if (!isset(self::$defaultStruct)) {
            self::$defaultStruct = new \SFW\NotifyStruct();

            self::$defaultStruct->e['config'] = self::$e['config'];

            self::$defaultStruct->e['sys'] = self::$e['sys'];

            register_shutdown_function(
                function (): void {
                    $this->complete();
                }
            );
        }

        self::$notifies[] = &$notify;

        $this->sys('Transaction')->onAbort(
            function () use (&$notify): void {
                $notify = null;
            }
        );
    }

    /**
     * Call build() method at all notifies and send all messages.
     */
    protected function complete(): void
    {
        while (self::$notifies) {
            $notify = array_shift(self::$notifies);

            if (!isset($notify)) {
                continue;
            }

            $structs = $notify->build(clone self::$defaultStruct);

            if (!self::$config['sys']['notifier']['enabled']) {
                continue;
            }

            foreach ($structs as $struct) {
                if (isset(self::$config['sys']['notifier']['recipients'])) {
                    $struct->recipients = self::$config['sys']['notifier']['recipients'];
                }

                $this->send($struct);
            }
        }
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
