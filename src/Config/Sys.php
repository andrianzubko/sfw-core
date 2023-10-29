<?php

namespace SFW\Config;

/**
 * Abstraction for system configuration.
 */
abstract class Sys extends \SFW\Config
{
    /**
     * Default system configuration.
     */
    protected static function defaults(): array
    {
        $config = [];

        // {{{ access control

        /* List of parameter names that will be available in templates.
         *
         * array
         */
        $config['shared'] = [];

        // }}}
        // {{{ general

        /* Environment mode.
         *
         * string 'dev', 'test', 'prod', etc..
         */
        $config['env'] = 'dev';

        /* Debug mode (not minify HTML/CSS/JS if true).
         *
         * bool
         */
        $config['debug'] = false;

        /* Basic url (autodetect if null).
         *
         * ?string
         */
        $config['url'] = null;

        /* Default timezone.
         *
         * string
         */
        $config['timezone'] = 'UTC';

        // }}}
        // {{{ databaser

        /* Default database.
         *
         * string 'Pgsql' or 'Mysql'
         */
        $config['db'] = 'Pgsql';

        /**
         * Pgsql.
         */
        $config['db_pgsql_host'] = 'localhost';

        $config['db_pgsql_port'] = 5432;

        $config['db_pgsql_db'] = null;

        $config['db_pgsql_user'] = null;

        $config['db_pgsql_pass'] = null;

        $config['db_pgsql_persistent'] = false;

        $config['db_pgsql_charset'] = 'utf-8';

        $config['db_pgsql_mode'] = \SFW\Databaser::ASSOC;

        /**
         * Mysql.
         */
        $config['db_mysql_host'] = 'localhost';

        $config['db_mysql_port'] = 3306;

        $config['db_mysql_db'] = null;

        $config['db_mysql_user'] = null;

        $config['db_mysql_pass'] = null;

        $config['db_mysql_persistent'] = false;

        $config['db_mysql_charset'] = 'utf8mb4';

        $config['db_mysql_mode'] = \SFW\Databaser::ASSOC;

        /* Log slow queries.
         *
         * ?string
         */
        $config['db_slow_queries_log'] = APP_DIR . '/var/log/slow.queries.log';

        /* Log slow queries with minimal time in seconds.
         *
         * float
         */
        $config['db_slow_queries_min'] = 0.5;

        // }}}
        // {{{ transaction

        /* How many times retry failed transactions with expected sql states.
         *
         * int
         */
        $config['transaction_retries'] = 7;

        /* Log transactions fails.
         *
         * ?string
         */
        $config['transaction_fails_log'] = APP_DIR . '/var/log/transaction.fails.log';

        // }}}
        // {{{ cacher

        /* Default cache.
         *
         * string 'Nocache', 'Apc', 'Memcached' or 'Redis'
         */
        $config['cacher'] = 'Apc';

        /* Apc.
         */
        $config['cacher_apc_ttl'] = 3600;

        $config['cacher_apc_ns'] = null;

        /* Memcached.
         */
        $config['cacher_memcached_ttl'] = 3600;

        $config['cacher_memcached_ns'] = null;

        $config['cacher_memcached_servers'] = [['127.0.0.1', 11211]];

        $config['cacher_memcached_options'] = null;

        /* Redis.
         */
        $config['cacher_redis_ttl'] = 3600;

        $config['cacher_redis_ns'] = null;

        $config['cacher_redis_connect'] = ['127.0.0.1', 6379, 2.5];

        $config['cacher_redis_options'] = null;

        // }}}
        // {{{ templater

        /* Default templater.
         *
         * string 'Native', 'Twig' or 'Xslt'
         */
        $config['templater'] = 'Native';

        /* Native
         */
        $config['templater_native_dir'] = APP_DIR . '/templates';

        $config['templater_native_minify'] = false;

        /* Twig
         */
        $config['templater_twig_dir'] = APP_DIR . '/templates';

        $config['templater_twig_cache'] = APP_DIR . '/var/cache/twig';

        $config['templater_twig_strict'] = true;

        /* Xslt
         */
        $config['templater_xslt_dir'] = APP_DIR . '/templates';

        $config['templater_xslt_root'] = 'root';

        $config['templater_xslt_item'] = 'item';

        // }}}
        // {{{ mailer

        /* Enable mailer.
         *
         * bool
         */
        $config['mailer'] = true;

        /* Default sender.
         *
         * 'EMAIL' or array('EMAIL'[, 'NAME']) or null
         */
        $config['mailer_sender'] = null;

        /* You can override recipients by your email for testing.
         *
         * ?array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
         */
        $config['mailer_recipients'] = null;

        /* Default replies.
         *
         * ?array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
         */
        $config['mailer_replies'] = null;

        // }}}
        // {{{ locker

        /* Lock files pattern.
         *
         * string
         */
        $config['locker_file'] = APP_DIR . '/var/locks/{KEY}.lock';

        // }}}
        // {{{ logger

        /* Additional errors log file.
         *
         * ?string
         */
        $config['logger_file'] = APP_DIR . '/var/log/errors.log';

        // }}}
        // {{{ response

        /* Compress output with only these mime types.
         *
         * Set null to disable output compression.
         *
         * ?array
         */
        $config['response_compress_mimes'] = [
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
        $config['response_compress_min'] = 32 * 1024;

        /* Optional error document files pattern.
         *
         * ?string
         */
        $config['response_error_document'] = APP_DIR . '/public/.bin/errors/{CODE}.html.php';

        /* Pattern for statistics to each html page generated by templater.
         *
         * ?string
         */
        $config['response_stats'] = '<!-- script {SCR_T} + sql({SQL_C}) {SQL_T} + tpl({TPL_C}) {TPL_T} = {ALL_T} -->';

        // }}}
        // {{{ dir

        /* Default mode for new directories.
         *
         * int
         */
        $config['dir_mode'] = 0777;

        // }}}
        // {{{ file

        /* Default mode for new/updated files.
         *
         * int
         */
        $config['file_mode'] = 0666;

        // }}}
        // {{{ merger

        /* Sources to merge (sources are absolute, targets are just filenames).
         *
         * Null value disables merger.
         *
         * ?array
         */
        $config['merger_sources'] = null;

        /* URL location with merged JS and CSS files.
         *
         * string
         */
        $config['merger_location'] = '/.merged';

        /* Directory for merged JS and CSS files.
         *
         * string
         */
        $config['merger_dir'] = APP_DIR . '/var/cache/merged';

        /* Cache file with merger internal data.
         *
         * string
         */
        $config['merger_cache'] = APP_DIR . '/var/cache/merger.php';

        // }}}
        // {{{ listeners provider

        /* Cache file with listeners provider internal data.
         *
         * string
         */
        $config['provider_cache'] = APP_DIR . '/var/cache/provider.php';

        // }}}
        // {{{ router

        /* Cache file with router internal data.
         *
         * string
         */
        $config['router_cache'] = APP_DIR . '/var/cache/router.php';

        // }}}
        // {{{ paginator

        /* Name of page parameter in url for paginator.
         *
         * ?string
         */
        $config['paginator_param'] = 'page';

        // }}}

        return $config;
    }
}
