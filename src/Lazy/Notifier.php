<?php

namespace SFW\Lazy;

/**
 * Notifier.
 */
class Notifier extends \SFW\Lazy
{
    /**
     * Prepared scructures.
     */
    protected array $structs = [];

    /**
     * Default environment for structures.
     */
    protected array $defaults;

    /**
     * Initializing default structure and at shutdown sender.
     */
    public function __construct()
    {
        $this->defaults = [
            'subject' => null,
            'sender' => [],
            'addresses' => [],
            'replies' => [],
            'customs' => [],
            'attachments' => [],
            'attachments_files' => [],
            'template' => null,
            'timezone' => null,
            'e' => [
                'system' => self::$e['system'],
                'config' => self::$e['config'],
            ],
        ];

        register_shutdown_function(
            function (string $cwd) {
                chdir($cwd);

                $this->send();
            }, getcwd()
        );
    }

    /**
     * Preparing notify with auto cleaner at transaction fails.
     */
    public function prepare(\SFW\Lazy\Notify $notify): void
    {
        $structs = $notify->prepare($this->defaults);

        foreach (array_keys($structs) as $i) {
            $this->structs[] = &$structs[$i];
        }

        $this->transaction()->onabort(
            function () use ($structs): void {
                foreach (array_keys($structs) as $i) {
                    $structs[$i] = null;
                }
            }
        );
    }

    /**
     * Sending notifies after browser connection aborting.
     */
    public function send(): void
    {
        foreach ($this->structs as $struct) {
            if (isset($struct) && count($struct['addresses'])) {
                try {
                    $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

                    $mailer->CharSet = 'utf-8';

                    if (isset($struct['subject'])) {
                        $mailer->Subject = $struct['subject'];
                    }

                    $mailer->setFrom(...$struct['sender']);

                    if (!empty(self::$config['mailer']['recipients'])) {
                        foreach (self::$config['mailer']['recipients'] as $email) {
                            try {
                                $mailer->addAddress($email);
                            } catch (\Exception $error) {}
                        }
                    } else {
                        foreach ($struct['addresses'] as $email => $name) {
                            try {
                                $mailer->addAddress($email, $name);
                            } catch (\Exception $error) {}
                        }
                    }

                    foreach ($struct['replies'] as $email => $name) {
                        try {
                            $mailer->addReplyTo($email, $name);
                        } catch (\Exception $error) {}
                    }

                    foreach ($struct['customs'] as $name => $value) {
                        $mailer->addCustomHeader($name, $value);
                    }

                    foreach ($struct['attachments'] as $filename => $contents) {
                        $mailer->addStringAttachment($contents, $filename);
                    }

                    foreach ($struct['attachments_files'] as $filename => $path) {
                        $mailer->addAttachment($path, $filename);
                    }

                    $mailer->msgHTML($this->out()->template($struct['e'], $struct['template'], true));

                    if (self::$config['mailer']['enabled']) {
                        $mailer->send();
                    }
                } catch (\Exception $error) {}
            }
        }

        $this->structs = [];
    }
}
