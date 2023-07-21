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
         * string 'dev', 'test', 'prod', etc..
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
        // {{{ databaser

        /* Default database (can be changed at runtime).
         *
         * string 'Pgsql' or 'Mysql'
         */
        $sys['db']['default'] = 'Pgsql';

        /**
         * Pgsql.
         */
        $sys['db']['pgsql']['host'] = 'localhost';

        $sys['db']['pgsql']['port'] = 5432;

        $sys['db']['pgsql']['db'] = null;

        $sys['db']['pgsql']['user'] = null;

        $sys['db']['pgsql']['pass'] = null;

        $sys['db']['pgsql']['persistent'] = false;

        $sys['db']['pgsql']['charset'] = 'utf-8';

        $sys['db']['pgsql']['mode'] = \SFW\Databaser::ASSOC;

        /**
         * Mysql.
         */
        $sys['db']['mysql']['host'] = 'localhost';

        $sys['db']['mysql']['port'] = 3306;

        $sys['db']['mysql']['db'] = null;

        $sys['db']['mysql']['user'] = null;

        $sys['db']['mysql']['pass'] = null;

        $sys['db']['mysql']['persistent'] = false;

        $sys['db']['mysql']['charset'] = 'utf8mb4';

        $sys['db']['mysql']['mode'] = \SFW\Databaser::ASSOC;

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

        /* How many times retry failed transactions with expected sql states.
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
        // {{{ cacher

        /* Default cache (can be changed at runtime).
         *
         * string 'Apc' or 'Memcached'
         */
        $sys['cacher']['default'] = 'Apc';

        /* Apc.
         */
        $sys['cacher']['apc']['ttl'] = 3600;

        $sys['cacher']['apc']['ns'] = md5(__FILE__);

        /* Memcached.
         */
        $sys['cacher']['memcached']['ttl'] = 3600;

        $sys['cacher']['memcached']['ns'] = md5(__FILE__);

        $sys['cacher']['memcached']['options'] = null;

        $sys['cacher']['memcached']['servers'] = [
            ['127.0.0.1', 11211],
        ];

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
