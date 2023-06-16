<?php

namespace SFW\Config;

/**
 * System configuration not available from templates.
 */
class Sys
{
    // {{{ database

    /**
     * Database driver.
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

    // }}}
    // {{{ mailer

    /**
     * Mailer enabled or not.
     */
    public bool $mailerEnabled = true;

    /**
     * Mailer default sender.
     */
    public array $mailerSender = [];

    /**
     * Mailer default replies.
     */
    public array $mailerReplies = [];

    /**
     * Mailer recipients (overriding all for testing).
     */
    public array $mailerRecipients = [];

    // }}}
    // {{{ recombiner

    /**
     * Recheck and recombine css and js files.
     */
    public bool $recombineCssAndJs = true;

    // }}}
}
