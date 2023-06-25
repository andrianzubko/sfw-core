<?php

namespace SFW\Config;

/**
 * System configuration not available from templates.
 */
class Sys
{
    // {{{ database

    /**
     * Default database driver (can be changed at runtime).
     */
    public string $db = 'mysql';

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
     * PostgreSQL.
     */
    public array $pgsql = [
        'connection' => '',
        'encoding' => 'utf-8',
        'persistent' => false,
    ];

    /**
     * MySQL.
     */
    public array $mysql = [
        'hostname' => null,
        'username' => null,
        'password' => null,
        'database' => null,
        'port' => null,
        'socket' => null,
        'charset' => 'utf8mb4',
    ];

    // }}}
    // {{{ cache

    /**
     * Default cache (can be changed at runtime).
     */
    public string $cache = 'memcached';

    /**
     * Apc.
     */
    public array $apc = [
        'ttl' => 3600,
        'ns' => null,
    ];

    /**
     * Memcached.
     */
    public array $memcached = [
        'ttl' => 3600,
        'ns' => null,
        'options' => [],
        'servers' => [],
    ];

    // }}}
    // {{{ mail

    /**
     * Enable mailer (if disabled, then build() method in notifies will be called, but no emails sent).
     */
    public bool $mailer = true;

    /**
     * Instead of disabling, you can replace recipients with these. Format: array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
     */
    public array $mailerReplaceRecipients = [];

    // }}}
    // {{{ frontend

    /**
     * Recombine css and js files (allways disable on production).
     */
    public bool $recombineCssAndJs = true;

    /**
     * Append stats to template via output class.
     */
    public bool $appendStatsToTemplate = true;

    // }}}
}
