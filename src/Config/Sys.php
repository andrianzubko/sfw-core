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

        /* Environment mode ('dev', 'test', 'prod', etc..).
         *
         * string
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

        /* PDO data source name.
         *
         * string
         */
        $sys['db']['dsn'] = null;

        /* Username for dsn string.
         *
         * ?string
         */
        $sys['db']['username'] = null;

        /* Password for dsn string.
         *
         * ?string
         */
        $sys['db']['password'] = null;

        /* Options for PDO module.
         *
         * ?array
         */
        $sys['db']['options'] = null;

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
        // {{{ transaction

        /* How many times to retry failed transactions with expected sql states.
         *
         * int
         */
        $sys['transaction']['retries'] = 7;

        /* Log transactions fails.
         *
         * ?string
         */
        $sys['transaction']['fails_log'] = APP_DIR . '/var/log/transaction.fails.log';

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
        // {{{ dir

        /* Default mode for new directories.
         *
         * int
         */
        $sys['dir']['mode'] = 0777;

        // }}}
        // {{{ file

        /* Default mode for new/updated files.
         *
         * int
         */
        $sys['file']['mode'] = 0666;

        // }}}
        // {{{ paginator

        /* Name of page param in url for paginator.
         *
         * string
         */
        $sys['paginator']['param'] = 'i';

        // }}}
        // {{{ templater

        /* Minify pages.
         *
         * bool
         */
        $sys['templater']['minify'] = true;

        /* Add statistics to each page (if called from Out class).
         *
         * bool
         */
        $sys['templater']['stats'] = true;

        // }}}

        return $sys;
    }
}
