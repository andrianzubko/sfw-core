<?php

namespace SFW;

/**
 * Message structure for Notify classes.
 */
class NotifyStruct
{
    /**
     * Subject.
     */
    public string $subject = '';

    /**
     * HTML body.
     */
    public string $body = '';

    /**
     * Sender address.
     *
     * 'EMAIL' or array('EMAIL'[, 'NAME']) or null
     */
    public array|string|null $sender = null;

    /**
     * Recipient addresses.
     *
     * array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
     */
    public array $recipients = [];

    /**
     * Reply To addresses.
     *
     * array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
     */
    public array $replies = [];

    /**
     * Custom headers.
     *
     * array('NAME: VALUE' or array('NAME', 'VALUE'), ...)
     */
    public array $customHeaders = [];

    /**
     * Attachments as files.
     *
     * array('PATH' or array('PATH'[, 'FILENAME']), ...)
     */
    public array $attachmentFiles = [];

    /**
     * Attachments as strings.
     *
     * array(array('BINARY', 'FILENAME'), ...)
     */
    public array $attachmentStrings = [];

    /**
     * Environment for template.
     */
    public array $e = [];
}
