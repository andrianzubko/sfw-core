<?php

namespace SFW\Config;

/**
 * System configuration not available from templates.
 */
class Sys
{
    /**
     * Database driver (PgSQL or MySQL).
     */
    public string $dbDriver = '';

    /**
     * Database connection options.
     */
    public array $dbOptions = [];

    /**
     * Log transactions fails.
     */
    public ?string $dbTransactionsFailsLog = 'log/transactions.fails.log';

    /**
     * Log slow queries.
     */
    public ?string $dbSlowQueriesLog = 'log/slow.queries.log';

    /**
     * Log slow queries with minimal time.
     */
    public float $dbSlowQueriesMin = 0.5;

    /**
     * Merge css and js files (allways disable on production).
     */
    public bool $mergeCssAndJs = true;

    /**
     * Cacher prefix (if not set, then md5(getcwd()) will be used).
     */
    public ?string $cacherPrefix = null;

    /**
     * Enable mailer (if disabled, then build() method in notifies will be called, but no emails sent).
     */
    public bool $mailer = true;

    /**
     * Instead of disabling, you can replace recipients with these. Format: array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
     */
    public array $mailerReplaceRecipients = [];
}
