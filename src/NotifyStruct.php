<?php

namespace SFW;

/**
 * Structure for notifies.
 */
class NotifyStruct
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
     * Sender address. Format: 'EMAIL' or array('EMAIL'[, 'NAME'])
     */
    public array|string $sender = [];

    /**
     * Recipient addresses. Format: array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
     */
    public array $recipients = [];

    /**
     * Reply To addresses. Format: array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
     */
    public array $replies = [];

    /**
     * Custom headers. Format: array('NAME: VALUE' or array('NAME', 'VALUE'), ...)
     */
    public array $customHeaders = [];

    /**
     * Attachments as files. Format: array('PATH' or array('PATH'[, 'FILENAME']), ...)
     */
    public array $attachmentsFiles = [];

    /**
     * Attachments as strings. Format: array(array('BINARY', 'FILENAME'), ...)
     */
    public array $attachmentsStrings = [];

    /**
     * Environment for template. By default here is copy of config and system environment.
     */
    public array $e = [];
}
