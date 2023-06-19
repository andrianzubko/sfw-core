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
    protected array $defaults;

    /**
     * Prepared notifies.
     */
    protected array $notifies = [];

    /**
     * Initializing default structure and registering shutdown finisher.
     */
    public function __construct()
    {
        $this->defaults = [
            'subject' => null,
            'body' => null,
            'sender' => [],
            'recipients' => [],
            'replies' => [],
            'customs' => [],
            'attachments' => [],
            'files' => [],
            'e' => [
                'system' => self::$e['system'],
                'config' => self::$e['config'],
            ],
        ];

        register_shutdown_function(
            function (string $cwd): void {
                chdir($cwd);

                $this->finish();
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
     * Call finish() method at all prepared notifies.
     */
    public function finish(): void
    {
        while ($this->notifies) {
            $notify = array_shift($this->notifies);

            if (isset($notify)) {
                $structs = $notify->finish($this->defaults);

                foreach ($structs as $struct) {
                    $this->send($struct);
                }
            }
        }
    }

    /**
     * Sending single message.
     */
    public function send(array $struct): void
    {
        $mailer = new \PHPMailer\PHPMailer\PHPMailer();

        $mailer->CharSet = 'utf-8';

        if (isset($struct['subject'])) {
            $mailer->Subject = $struct['subject'];
        }

        if (isset($struct['body'])) {
            $mailer->msgHTML($struct['body']);
        }

        if ($struct['sender']) {
            $mailer->setFrom(...$struct['sender']);
        }

        if (self::$config['mailerRecipients']) {
            foreach (self::$config['mailerRecipients'] as $recipient) {
                $mailer->addAddress(...$recipient);
            }
        } else {
            foreach ($struct['recipients'] as $recipient) {
                $mailer->addAddress(...$recipient);
            }
        }

        foreach ($struct['replies'] as $reply) {
            $mailer->addReplyTo(...$reply);
        }

        foreach ($struct['customs'] as $custom) {
            $mailer->addCustomHeader(...$custom);
        }

        foreach ($struct['attachments'] as $attachment) {
            $mailer->addStringAttachment(...$attachment);
        }

        foreach ($struct['files'] as $file) {
            $mailer->addAttachment(...$file);
        }

        $mailer->send();
    }
}
