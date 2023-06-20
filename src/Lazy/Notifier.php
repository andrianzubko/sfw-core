<?php

namespace SFW\Lazy;

/**
 * Notifier.
 */
class Notifier extends \SFW\Lazy
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
     * Initializing default structure and registering shutdown function to build and send all messages.
     */
    public function __construct()
    {
        $this->defaultStruct = new \SFW\NotifyStruct();

        $this->defaultStruct->e['config'] = self::$e['config'];

        $this->defaultStruct->e['system'] = self::$e['system'];

        register_shutdown_function(
            function (string $cwd): void {
                chdir($cwd);

                $this->complete();
            }, getcwd()
        );
    }

    /**
     * Preparing notify with auto cleaner at transaction fails.
     */
    public function prepare(string $name, ...$arguments): void
    {
        $notify = new ("App\\Notify\\$name")(...$arguments);

        $this->notifies[] = &$notify;

        $this->transaction()->onabort(
            function () use (&$notify): void {
                $notify = null;
            }
        );
    }

    /**
     * Call build() method at all notifies and send all messages.
     */
    protected function complete()
    {
        while ($this->notifies) {
            $notify = array_shift($this->notifies);

            if (isset($notify)) {
                $structs = $notify->build(clone $this->defaultStruct);

                if (self::$config['mailer']) {
                    foreach ($structs as $struct) {
                        if (self::$config['mailerReplaceRecipients']) {
                            $struct->recipients = self::$config['mailerReplaceRecipients'];
                        }

                        $this->send($struct);
                    }
                }
            }
        }
    }

    /**
     * Sending single message.
     */
    protected function send(\SFW\NotifyStruct $struct): void
    {
        $mailer = new \PHPMailer\PHPMailer\PHPMailer();

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
            if (is_array($recipient)) {
                $mailer->addAddress(...$recipient);
            } else {
                $mailer->addAddress($recipient);
            }
        }

        foreach ($struct->replies as $reply) {
            if (is_array($reply)) {
                $mailer->addReplyTo(...$reply);
            } else {
                $mailer->addReplyTo($reply);
            }
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
    }
}
