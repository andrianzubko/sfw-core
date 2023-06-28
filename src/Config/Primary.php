<?php

namespace SFW\Config;

/**
 * Primary configuration not available from templates.
 */
class Primary
{
    // {{{ database

    /**
     * Default database driver (can be changed at runtime).
     */
    public string $db = 'mysql';

    /**
     * Log transactions fails.
     */
    public ?string $db_transactions_fails_log = 'log/transactions.fails.log';

    /**
     * Log slow queries.
     */
    public ?string $db_slow_queries_log = 'log/slow.queries.log';

    /**
     * Log slow queries with minimal time.
     */
    public float $db_slow_queries_min = 0.5;

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
     * Instead of disabling, you can replace recipients with these.
     * 
     * array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
     */
    public array $mailer_replace_recipients = [];

    // }}}
    // {{{ frontend

    /**
     * Recombine css and js files (allways disable on production).
     */
    public bool $recombine_css_and_js = true;

    /**
     * Add statistics to each page generated from template.
     */
    public bool $add_stats_to_page = true;

    // }}}
}
