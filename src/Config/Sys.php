<?php

namespace SFW\Config;

/**
 * System configuration not available from templates.
 */
abstract class Sys
{
    // {{{ frontend

    /**
     * Basic url of site (autodetect if not set).
     */
    public ?string $basicUrl = null;

    /**
     * Recombine css and js files (always disable on production).
     */
    public bool $recombineCssAndJs = true;

    /**
     * Add statistics to each page generated from template.
     */
    public bool $addStatsToPage = true;

    // }}}
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
    public string $cache = 'apc';

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
     * Enable mailer.
     *
     * If disabled, then no emails will be sent, but build() method in Notify classes will be called anyway.
     */
    public bool $mailer = true;

    /**
     * Instead of disabling, you can replace recipients to your email.
     * 
     * array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
     */
    public array $mailerReplaceRecipients = [];

    // }}}
    // {{{ time

    /**
     * Default timezone.
     */
    public string $timezone = 'Europe/Moscow';

    // }}}
}
