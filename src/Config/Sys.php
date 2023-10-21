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

        /* Basic url (autodetect if null).
         *
         * ?string
         */
        $sys['url'] = null;

        /* Default timezone.
         *
         * string
         */
        $sys['timezone'] = 'UTC';

        // }}}
        // {{{ databaser

        /* Default database.
         *
         * string 'Pgsql' or 'Mysql'
         */
        $sys['db_default'] = 'Pgsql';

        /**
         * Pgsql.
         */
        $sys['db_pgsql_host'] = 'localhost';

        $sys['db_pgsql_port'] = 5432;

        $sys['db_pgsql_db'] = null;

        $sys['db_pgsql_user'] = null;

        $sys['db_pgsql_pass'] = null;

        $sys['db_pgsql_persistent'] = false;

        $sys['db_pgsql_charset'] = 'utf-8';

        $sys['db_pgsql_mode'] = \SFW\Databaser::ASSOC;

        /**
         * Mysql.
         */
        $sys['db_mysql_host'] = 'localhost';

        $sys['db_mysql_port'] = 3306;

        $sys['db_mysql_db'] = null;

        $sys['db_mysql_user'] = null;

        $sys['db_mysql_pass'] = null;

        $sys['db_mysql_persistent'] = false;

        $sys['db_mysql_charset'] = 'utf8mb4';

        $sys['db_mysql_mode'] = \SFW\Databaser::ASSOC;

        /* Log slow queries.
         *
         * ?string
         */
        $sys['db_slow_queries_log'] = APP_DIR . '/var/log/slow.queries.log';

        /* Log slow queries with minimal time in seconds.
         *
         * float
         */
        $sys['db_slow_queries_min'] = 0.5;

        // }}}
        // {{{ transaction

        /* How many times retry failed transactions with expected sql states.
         *
         * int
         */
        $sys['transaction_retries'] = 7;

        /* Log transactions fails.
         *
         * ?string
         */
        $sys['transaction_fails_log'] = APP_DIR . '/var/log/transaction.fails.log';

        // }}}
        // {{{ cacher

        /* Default cache.
         *
         * string 'Nocache', 'Apc', 'Memcached' or 'Redis'
         */
        $sys['cacher_default'] = 'Apc';

        /* Apc.
         */
        $sys['cacher_apc_ttl'] = 3600;

        $sys['cacher_apc_ns'] = md5(__FILE__);

        /* Memcached.
         */
        $sys['cacher_memcached_ttl'] = $sys['cacher_apc_ttl'];

        $sys['cacher_memcached_ns'] = $sys['cacher_apc_ns'];

        $sys['cacher_memcached_servers'] = [['127.0.0.1', 11211]];

        $sys['cacher_memcached_options'] = null;

        /* Redis.
         */
        $sys['cacher_redis_ttl'] = $sys['cacher_apc_ttl'];

        $sys['cacher_redis_ns'] = $sys['cacher_apc_ns'];

        $sys['cacher_redis_connect'] = ['127.0.0.1', 6379, 2.5];

        $sys['cacher_redis_options'] = null;

        // }}}
        // {{{ templater

        /* Default templater.
         *
         * string 'Native', 'Twig' or 'Xslt'
         */
        $sys['templater_default'] = 'Native';

        /* Native
         */
        $sys['templater_native_dir'] = APP_DIR . '/templates';

        $sys['templater_native_minify'] = false;

        /* Twig
         */
        $sys['templater_twig_dir'] = APP_DIR . '/templates';

        $sys['templater_twig_cache'] = APP_DIR . '/var/cache/twig';

        $sys['templater_twig_strict'] = true;

        /* Xslt
         */
        $sys['templater_xslt_dir'] = APP_DIR . '/templates';

        $sys['templater_xslt_root'] = 'root';

        $sys['templater_xslt_item'] = 'item';

        // }}}
        // {{{ notifier

        /* Enable notifier.
         *
         * Build() method in Notify classes will be called even if notifier disabled.
         *
         * bool
         */
        $sys['notifier_enabled'] = true;

        /* Instead of disabling, you can override recipients by your email.
         *
         * ?array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
         */
        $sys['notifier_recipients'] = null;

        /* Default sender.
         *
         * 'EMAIL' or array('EMAIL'[, 'NAME']) or null
         */
        $sys['notifier_sender'] = null;

        /* Default replies.
         *
         * array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
         */
        $sys['notifier_replies'] = [];

        // }}}
        // {{{ locker

        /* Lock files pattern.
         *
         * string
         */
        $sys['locker_file'] = APP_DIR . '/var/locks/{KEY}.lock';

        // }}}
        // {{{ logger

        /* Additional errors log file.
         *
         * ?string
         */
        $sys['logger_file'] = APP_DIR . '/var/log/errors.log';

        // }}}
        // {{{ response

        /* Compress output with only these mime types.
         *
         * Set null to disable output compression.
         *
         * ?array
         */
        $sys['response_compress_mimes'] = [
            'text/html',
            'text/plain',
            'text/xml',
            'text/css',
            'application/x-javascript',
            'application/javascript',
            'application/ecmascript',
            'application/rss+xml',
            'application/xml',
        ];

        /* Compress output if size more this value in bytes.
         *
         * int
         */
        $sys['response_compress_min'] = 32 * 1024;

        /* Optional error document files pattern.
         *
         * ?string
         */
        $sys['response_error_document'] = APP_DIR . '/public/.bin/errors/{CODE}.html.php';

        /* Pattern for statistics to each html page generated by templater.
         *
         * ?string
         */
        $sys['response_stats'] = '<!-- script {SCR_T} + sql({SQL_C}) {SQL_T} + tpl({TPL_C}) {TPL_T} = {ALL_T} -->';

        // }}}
        // {{{ dir

        /* Default mode for new directories.
         *
         * int
         */
        $sys['dir_mode'] = 0777;

        // }}}
        // {{{ file

        /* Default mode for new/updated files.
         *
         * int
         */
        $sys['file_mode'] = 0666;

        // }}}
        // {{{ merger

        /* Sources to merge (sources are absolute, targets are just filenames).
         *
         * Null value disables merger.
         *
         * ?array
         */
        $sys['merger_sources'] = null;

        /* URL location with merged JS and CSS files.
         *
         * string
         */
        $sys['merger_location'] = '/.merged';

        /* Directory for merged JS and CSS files.
         *
         * string
         */
        $sys['merger_dir'] = APP_DIR . '/var/cache/merged';

        /* Cache file with merger internal data.
         *
         * string
         */
        $sys['merger_cache'] = APP_DIR . '/var/cache/merger.php';

        // }}}
        // {{{ router

        /* Cache file with router internal data.
         *
         * string
         */
        $sys['router_cache'] = APP_DIR . '/var/cache/router.php';

        // }}}
        // {{{ paginator

        /* Name of page parameter in url for paginator.
         *
         * ?string
         */
        $sys['paginator_param'] = 'page';

        // }}}

        return $sys;
    }
}
