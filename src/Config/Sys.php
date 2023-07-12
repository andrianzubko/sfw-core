<?php

namespace SFW\Config;

/**
 * System config (not available from templates).
 */
class Sys extends \SFW\Config
{
    /**
     * Returns array with config parameters.
     */
    public static function get(): array
    {
        $sys = [];

        // {{{ general

        /* Environment mode.
         *
         * string 'dev', 'prod', etc...
         */
        $sys['env'] = 'dev';

        /* Debug mode (not minify HTML/CSS/JS if true).
         *
         * bool
         */
        $sys['debug'] = false;

        /* Basic url (autodetect if not set).
         *
         * ?string
         */
        $sys['url'] = null;

        /* Default timezone.
         *
         * string
         */
        $sys['timezone'] = 'Europe/Moscow';

        // }}}
        // {{{ database

        /* Default database driver (can be changed at runtime).
         *
         * string 'Pgsql' or 'Mysql'
         */
        $sys['db']['default'] = 'Pgsql';

        /* How many times to retry failed transactions with expected sql states.
         *
         * int
         */
        $sys['db']['transactions_retries'] = 7;

        /* Log transactions fails.
         *
         * ?string
         */
        $sys['db']['transactions_fails_log'] = APP_DIR . '/var/log/transactions.fails.log';

        /* Log slow queries.
         *
         * ?string
         */
        $sys['db']['slow_queries_log'] = APP_DIR . '/var/log/slow.queries.log';

        /* Log slow queries with minimal time in seconds.
         *
         * float
         */
        $sys['db']['slow_queries_min'] = 0.5;

        // }}}
        // {{{ database: postgresql

        /*
         * Postresql connection string.
         *
         * string
         */
        $sys['db']['pgsql']['connection'] = '';

        /* Postgresql encoding (utf-8 if not set).
         *
         * ?string
         */
        $sys['db']['pgsql']['encoding'] = null;

        /* Postgresql persistent connection (use pgbouncer instead).
         *
         * bool
         */
        $sys['db']['pgsql']['persistent'] = false;

        // }}}
        // {{{ database: mysql

        /* Mysql hostname.
         *
         * ?string
         */
        $sys['db']['mysql']['hostname'] = null;

        /* Mysql username.
         *
         * ?string
         */
        $sys['db']['mysql']['username'] = null;

        /* Mysql password.
         *
         * ?string
         */
        $sys['db']['mysql']['password'] = null;

        /* Mysql database name.
         *
         * ?string
         */
        $sys['db']['mysql']['database'] = null;

        /* Mysql port (3306 if not set).
         *
         * ?int
         */
        $sys['db']['mysql']['port'] = null;

        /* Mysql socket.
         *
         * ?string
         */
        $sys['db']['mysql']['socket'] = null;

        /* Mysql charset (utf8mb4 if not set).
         *
         * ?string
         */
        $sys['db']['mysql']['charset'] = null;

        // }}}
        // {{{ cache

        /* Default cache (can be changed at runtime).
         *
         * string 'Apc' or 'Memcached'
         */
        $sys['cache']['default'] = 'Apc';

        // }}}
        // {{{ cache: apc

        /* Apc default TTL.
         *
         * int
         */
        $sys['cache']['apc']['ttl'] = 3600;

        /* Apc namespace (auto if not set).
         *
         * ?string
         */
        $sys['cache']['apc']['ns'] = null;

        // }}}
        // {{{ cache: memcached

        /* Memcached default TTl.
         *
         * int
         */
        $sys['cache']['memcached']['ttl'] = 3600;

        /* Memcached namespace (auto if not set).
         *
         * ?string
         */
        $sys['cache']['memcached']['ns'] = null;

        /* Memcached options.
         *
         * ?array(KEY => VALUE, ...)
         */
        $sys['cache']['memcached']['options'] = null;

        /* Memcached servers (127.0.0.1:11211 if not set).
         *
         * ?array(array('HOST', PORT), ...)
         */
        $sys['cache']['memcached']['servers'] = null;

        // }}}
        // {{{ notifier

        /* Enable notifier.
         *
         * If disabled, then no emails will be sent, but build() method in Notify classes will be called anyway.
         *
         * bool
         */
        $sys['notifier']['enabled'] = true;

        /* Instead of disabling, you can override recipients by your email.
         *
         * ?array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
         */
        $sys['notifier']['recipients'] = null;

        // }}}
        // {{{ locker

        /* Lock files pattern.
         *
         * string
         */
        $sys['locker']['pattern'] = APP_DIR . '/var/locks/%s.lock';

        // }}}
        // {{{ paginator

        /* Name of page param in url for paginator.
         *
         * string
         */
        $sys['paginator']['param'] = 'i';

        // }}}
        // {{{ templater

        /* Add statistics to each page generated by template.
         *
         * bool
         */
        $sys['templater']['stats'] = true;

        // }}}

        return $sys;
    }
}
