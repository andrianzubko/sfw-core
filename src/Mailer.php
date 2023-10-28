<?php

namespace SFW;

use PHPMailer\PHPMailer\{PHPMailer, Exception AS PHPMailerException};
use SFW\Exception\{BadConfiguration, InvalidArgument, Logic, Runtime};

/**
 * Mailer.
 */
class Mailer extends Base
{
    /**
     * PHPMailer instance.
     */
    protected PHPMailer $mailer;

    /**
     * Strict mode flag.
     */
    protected bool $strict = true;

    /**
     * Instantiates PHPMailer with default parameters from configuration.
     *
     * In strict mode adding recipients, cc, replies and sending message will throw errors.
     *
     * @throws BadConfiguration
     */
    public function __construct(bool $strict = false)
    {
        $this->mailer = new PHPMailer(true);

        $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;

        try {
            if (self::$sys['config']['mailer_sender'] !== null) {
                $this->setFrom(...(array) self::$sys['config']['mailer_sender']);
            }

            if (self::$sys['config']['mailer_recipients'] !== null) {
                $this->addRecipients(self::$sys['config']['mailer_recipients']);
            }

            if (self::$sys['config']['mailer_replies'] !== null) {
                $this->addReplies(self::$sys['config']['mailer_replies']);
            }
        } catch (InvalidArgument $e) {
            throw new BadConfiguration($e->getMessage());
        }

        $this->strict = $strict;
    }

    /**
     * Sets from.
     *
     * @throws InvalidArgument
     */
    public function setFrom(string $email, ?string $name = null): self
    {
        try {
            $this->mailer->setFrom($email, $name ?? '');
        } catch (PHPMailerException $e) {
            throw new InvalidArgument($e->getMessage());
        }

        return $this;
    }

    /**
     * Adds recipient.
     *
     * @throws InvalidArgument
     */
    public function addRecipient(string $email, ?string $name = null): self
    {
        try {
            $this->mailer->addAddress($email, $name ?? '');
        } catch (PHPMailerException $e) {
            if ($this->strict) {
                throw new InvalidArgument($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Sets recipient.
     *
     * @throws InvalidArgument
     */
    public function setRecipient(string $email, ?string $name = null): self
    {
        $this->mailer->clearAddresses();

        return $this->addRecipient($email, $name);
    }

    /**
     * Adds recipients.
     *
     * @throws InvalidArgument
     */
    public function addRecipients(array $recipients): self
    {
        foreach ($recipients as $recipient) {
            $this->addRecipient(...(array) $recipient);
        }

        return $this;
    }

    /**
     * Sets recipients.
     *
     * @throws InvalidArgument
     */
    public function setRecipients(array $recipients = []): self
    {
        $this->mailer->clearAddresses();

        return $this->addRecipients($recipients);
    }

    /**
     * Adds CC.
     *
     * @throws InvalidArgument
     */
    public function addCC(string $email, ?string $name = null): self
    {
        try {
            $this->mailer->addCC($email, $name ?? '');
        } catch (PHPMailerException $e) {
            if ($this->strict) {
                throw new InvalidArgument($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Sets CC.
     *
     * @throws InvalidArgument
     */
    public function setCC(string $email, ?string $name = null): self
    {
        $this->mailer->clearCCs();

        return $this->addCC($email, $name);
    }

    /**
     * Adds CC's.
     *
     * @throws InvalidArgument
     */
    public function addCCs(array $copies): self
    {
        foreach ($copies as $copy) {
            $this->addCC(...(array) $copy);
        }

        return $this;
    }

    /**
     * Sets CC's.
     *
     * @throws InvalidArgument
     */
    public function setCCs(array $copies = []): self
    {
        $this->mailer->clearCCs();

        return $this->addCCs($copies);
    }

    /**
     * Adds reply.
     *
     * @throws InvalidArgument
     */
    public function addReply(string $email, ?string $name = null): self
    {
        try {
            $this->mailer->addReplyTo($email, $name ?? '');
        } catch (PHPMailerException $e) {
            if ($this->strict) {
                throw new InvalidArgument($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Sets reply.
     *
     * @throws InvalidArgument
     */
    public function setReply(string $email, ?string $name = null): self
    {
        $this->mailer->clearReplyTos();

        return $this->addReply($email, $name);
    }

    /**
     * Adds replies.
     *
     * @throws InvalidArgument
     */
    public function addReplies(array $replies): self
    {
        foreach ($replies as $reply) {
            $this->addReply(...(array) $reply);
        }

        return $this;
    }

    /**
     * Sets replies.
     *
     * @throws InvalidArgument
     */
    public function setReplies(array $replies = []): self
    {
        $this->mailer->clearReplyTos();

        return $this->addReplies($replies);
    }

    /**
     * Adds custom header.
     *
     * @throws InvalidArgument
     */
    public function addCustomHeader(string $name, ?string $value = null): self
    {
        try {
            $this->mailer->addCustomHeader($name, $value);
        } catch (PHPMailerException $e) {
            throw new InvalidArgument($e->getMessage());
        }

        return $this;
    }

    /**
     * Sets custom header.
     *
     * @throws InvalidArgument
     */
    public function setCustomHeader(string $name, ?string $value = null): self
    {
        $this->mailer->clearCustomHeaders();

        return $this->addCustomHeader($name, $value);
    }

    /**
     * Adds custom headers.
     *
     * @throws InvalidArgument
     */
    public function addCustomHeaders(array $headers): self
    {
        foreach ($headers as $header) {
            $this->addCustomHeader(...(array) $header);
        }

        return $this;
    }

    /**
     * Sets custom headers.
     *
     * @throws InvalidArgument
     */
    public function setCustomHeaders(array $headers = []): self
    {
        $this->mailer->clearCustomHeaders();

        return $this->addCustomHeaders($headers);
    }

    /**
     * Sets subject.
     */
    public function setSubject(string $subject): self
    {
        $this->mailer->Subject = $subject;

        return $this;
    }

    /**
     * Sets body.
     *
     * @throws InvalidArgument
     */
    public function setBody(string $body, bool $isHtml = true): self
    {
        if ($isHtml) {
            try {
                $this->mailer->msgHTML($body);
            } catch (PHPMailerException $e) {
                throw new InvalidArgument($e->getMessage());
            }
        } else {
            $this->mailer->Body = $body;
        }

        return $this;
    }

    /**
     * Adds attachment file.
     *
     * @throws InvalidArgument
     */
    public function addAttachmentFile(string $path, ?string $name = null, ?string $type = null): self
    {
        try {
            $this->mailer->addAttachment($path, $name ?? '', type: $type ?? '');
        } catch (PHPMailerException $e) {
            throw new InvalidArgument($e->getMessage());
        }

        return $this;
    }

    /**
     * Sets attachment file.
     *
     * @throws InvalidArgument
     */
    public function setAttachmentFile(string $path, ?string $name = null, ?string $type = null): self
    {
        $this->mailer->clearAttachments();

        return $this->addAttachmentFile($path, $name, $type);
    }

    /**
     * Adds attachment files.
     *
     * @throws InvalidArgument
     */
    public function addAttachmentFiles(array $attachments): self
    {
        foreach ($attachments as $attachment) {
            $this->addAttachmentFile(...(array) $attachment);
        }

        return $this;
    }

    /**
     * Sets attachment files.
     *
     * @throws InvalidArgument
     */
    public function setAttachmentFiles(array $attachments = []): self
    {
        $this->mailer->clearAttachments();

        return $this->addAttachmentFiles($attachments);
    }

    /**
     * Adds attachment string as file.
     *
     * @throws InvalidArgument
     */
    public function addAttachmentString(string $contents, string $filename, ?string $type = null): self
    {
        try {
            $this->mailer->addStringAttachment($contents, $filename, type: $type ?? '');
        } catch (PHPMailerException $e) {
            throw new InvalidArgument($e->getMessage());
        }

        return $this;
    }

    /**
     * Sets attachment string as file.
     *
     * @throws InvalidArgument
     */
    public function setAttachmentString(string $contents, string $filename, ?string $type = null): self
    {
        $this->mailer->clearAttachments();

        return $this->addAttachmentString($contents, $filename, $type);
    }

    /**
     * Adds attachment strings as files.
     *
     * @throws InvalidArgument
     */
    public function addAttachmentStrings(array $attachments): self
    {
        foreach ($attachments as $attachment) {
            $this->addAttachmentString(...(array) $attachment);
        }

        return $this;
    }

    /**
     * Sets attachment strings as files.
     *
     * @throws InvalidArgument
     */
    public function setAttachmentStrings(array $attachments): self
    {
        $this->mailer->clearAttachments();

        return $this->addAttachmentStrings($attachments);
    }

    /**
     * Creates message and sends it.
     *
     * @throws Logic
     * @throws Runtime
     */
    public function send(): bool
    {
        if (!$this->mailer->getToAddresses()) {
            if ($this->strict) {
                throw new Logic('Must be at least one recipient');
            } else {
                return false;
            }
        }

        if (self::$sys['config']['mailer']) {
            try {
                return $this->mailer->send();
            } catch (PHPMailerException $e) {
                if ($this->strict) {
                    throw new Runtime($e->getMessage());
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}
