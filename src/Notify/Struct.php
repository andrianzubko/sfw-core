<?php

namespace SFW\Notify;

/**
 * Message structure for Notify classes.
 */
class Struct
{
    /**
     * Subject.
     */
    public ?string $subject = null;

    /**
     * HTML body.
     */
    public ?string $body = null;

    /**
     * Sender address.
     *
     * 'EMAIL' or array('EMAIL'[, 'NAME'])
     */
    public array|string $sender = [];

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
    public array $attachmentsFiles = [];

    /**
     * Attachments as strings.
     *
     * array(array('BINARY', 'FILENAME'), ...)
     */
    public array $attachmentsStrings = [];

    /**
     * Environment for template.
     */
    public array $e = [];
}
