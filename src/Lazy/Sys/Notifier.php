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

            $this->sys('Shutdown')->register(
                $this->complete(...)
            );
        }

        $this->notifies[] = &$notify;

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
        while ($this->notifies) {
            $notify = array_shift($this->notifies);

            if (!isset($notify)) {
                continue;
            }

            try {
                $structs = $notify->build(clone $this->defaultStruct);
            } catch (\Throwable $error) {
                $this->sys('Logger')->error($error);

                continue;
            }

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
