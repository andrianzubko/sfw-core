<?php

declare(strict_types=1);

namespace SFW;

use PHPMailer\PHPMailer\{PHPMailer, Exception AS PHPMailerException};

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
     * @throws Exception\BadConfiguration
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
        } catch (Exception\InvalidArgument $e) {
            throw new Exception\BadConfiguration($e->getMessage());
        }

        $this->strict = $strict;
    }

    /**
     * Sets from.
     *
     * @throws Exception\InvalidArgument
     */
    public function setFrom(string $email, ?string $name = null): self
    {
        try {
            $this->mailer->setFrom($email, $name ?? '');
        } catch (PHPMailerException $e) {
            throw new Exception\InvalidArgument($e->getMessage());
        }

        return $this;
    }

    /**
     * Adds recipient.
     *
     * @throws Exception\InvalidArgument
     */
    public function addRecipient(string $email, ?string $name = null): self
    {
        try {
            $this->mailer->addAddress($email, $name ?? '');
        } catch (PHPMailerException $e) {
            if ($this->strict) {
                throw new Exception\InvalidArgument($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Sets recipient.
     *
     * @throws Exception\InvalidArgument
     */
    public function setRecipient(string $email, ?string $name = null): self
    {
        $this->mailer->clearAddresses();

        return $this->addRecipient($email, $name);
    }

    /**
     * Adds recipients.
     *
     * @throws Exception\InvalidArgument
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
     * @throws Exception\InvalidArgument
     */
    public function setRecipients(array $recipients = []): self
    {
        $this->mailer->clearAddresses();

        return $this->addRecipients($recipients);
    }

    /**
     * Adds CC.
     *
     * @throws Exception\InvalidArgument
     */
    public function addCC(string $email, ?string $name = null): self
    {
        try {
            $this->mailer->addCC($email, $name ?? '');
        } catch (PHPMailerException $e) {
            if ($this->strict) {
                throw new Exception\InvalidArgument($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Sets CC.
     *
     * @throws Exception\InvalidArgument
     */
    public function setCC(string $email, ?string $name = null): self
    {
        $this->mailer->clearCCs();

        return $this->addCC($email, $name);
    }

    /**
     * Adds CC's.
     *
     * @throws Exception\InvalidArgument
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
     * @throws Exception\InvalidArgument
     */
    public function setCCs(array $copies = []): self
    {
        $this->mailer->clearCCs();

        return $this->addCCs($copies);
    }

    /**
     * Adds reply.
     *
     * @throws Exception\InvalidArgument
     */
    public function addReply(string $email, ?string $name = null): self
    {
        try {
            $this->mailer->addReplyTo($email, $name ?? '');
        } catch (PHPMailerException $e) {
            if ($this->strict) {
                throw new Exception\InvalidArgument($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Sets reply.
     *
     * @throws Exception\InvalidArgument
     */
    public function setReply(string $email, ?string $name = null): self
    {
        $this->mailer->clearReplyTos();

        return $this->addReply($email, $name);
    }

    /**
     * Adds replies.
     *
     * @throws Exception\InvalidArgument
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
     * @throws Exception\InvalidArgument
     */
    public function setReplies(array $replies = []): self
    {
        $this->mailer->clearReplyTos();

        return $this->addReplies($replies);
    }

    /**
     * Adds custom header.
     *
     * @throws Exception\InvalidArgument
     */
    public function addCustomHeader(string $name, ?string $value = null): self
    {
        try {
            $this->mailer->addCustomHeader($name, $value);
        } catch (PHPMailerException $e) {
            throw new Exception\InvalidArgument($e->getMessage());
        }

        return $this;
    }

    /**
     * Sets custom header.
     *
     * @throws Exception\InvalidArgument
     */
    public function setCustomHeader(string $name, ?string $value = null): self
    {
        $this->mailer->clearCustomHeaders();

        return $this->addCustomHeader($name, $value);
    }

    /**
     * Adds custom headers.
     *
     * @throws Exception\InvalidArgument
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
     * @throws Exception\InvalidArgument
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
     * @throws Exception\InvalidArgument
     */
    public function setBody(string $body, bool $isHtml = true): self
    {
        if ($isHtml) {
            try {
                $this->mailer->msgHTML($body);
            } catch (PHPMailerException $e) {
                throw new Exception\InvalidArgument($e->getMessage());
            }
        } else {
            $this->mailer->Body = $body;
        }

        return $this;
    }

    /**
     * Adds attachment file.
     *
     * @throws Exception\InvalidArgument
     */
    public function addAttachmentFile(string $path, ?string $name = null, ?string $type = null): self
    {
        try {
            $this->mailer->addAttachment($path, $name ?? '', type: $type ?? '');
        } catch (PHPMailerException $e) {
            throw new Exception\InvalidArgument($e->getMessage());
        }

        return $this;
    }

    /**
     * Sets attachment file.
     *
     * @throws Exception\InvalidArgument
     */
    public function setAttachmentFile(string $path, ?string $name = null, ?string $type = null): self
    {
        $this->mailer->clearAttachments();

        return $this->addAttachmentFile($path, $name, $type);
    }

    /**
     * Adds attachment files.
     *
     * @throws Exception\InvalidArgument
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
     * @throws Exception\InvalidArgument
     */
    public function setAttachmentFiles(array $attachments = []): self
    {
        $this->mailer->clearAttachments();

        return $this->addAttachmentFiles($attachments);
    }

    /**
     * Adds attachment string as file.
     *
     * @throws Exception\InvalidArgument
     */
    public function addAttachmentString(string $contents, string $filename, ?string $type = null): self
    {
        try {
            $this->mailer->addStringAttachment($contents, $filename, type: $type ?? '');
        } catch (PHPMailerException $e) {
            throw new Exception\InvalidArgument($e->getMessage());
        }

        return $this;
    }

    /**
     * Sets attachment string as file.
     *
     * @throws Exception\InvalidArgument
     */
    public function setAttachmentString(string $contents, string $filename, ?string $type = null): self
    {
        $this->mailer->clearAttachments();

        return $this->addAttachmentString($contents, $filename, $type);
    }

    /**
     * Adds attachment strings as files.
     *
     * @throws Exception\InvalidArgument
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
     * @throws Exception\InvalidArgument
     */
    public function setAttachmentStrings(array $attachments): self
    {
        $this->mailer->clearAttachments();

        return $this->addAttachmentStrings($attachments);
    }

    /**
     * Creates message and sends it.
     *
     * @throws Exception\Logic
     * @throws Exception\Runtime
     */
    public function send(): bool
    {
        if (!$this->mailer->getToAddresses()) {
            if ($this->strict) {
                throw new Exception\Logic('Must be at least one recipient');
            } else {
                return false;
            }
        }

        if (self::$sys['config']['mailer']) {
            try {
                return $this->mailer->send();
            } catch (PHPMailerException $e) {
                if ($this->strict) {
                    throw new Exception\Runtime($e->getMessage());
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}
