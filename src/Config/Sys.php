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
        $sys['timezone'] = 'Europe/Moscow';

        // }}}
        // {{{ databaser

        /* Default database.
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

        /* Default cache.
         *
         * string 'Nocache', 'Apc', 'Memcached' or 'Redis'
         */
        $sys['cacher']['default'] = 'Apc';

        /* Apc.
         */
        $sys['cacher']['apc']['ttl'] = 3600;

        $sys['cacher']['apc']['ns'] = md5(__FILE__);

        /* Memcached.
         */
        $sys['cacher']['memcached']['ttl'] = $sys['cacher']['apc']['ttl'];

        $sys['cacher']['memcached']['ns'] = $sys['cacher']['apc']['ns'];

        $sys['cacher']['memcached']['servers'] = [['127.0.0.1', 11211]];

        $sys['cacher']['memcached']['options'] = null;

        /* Redis.
         */
        $sys['cacher']['redis']['ttl'] = $sys['cacher']['apc']['ttl'];

        $sys['cacher']['redis']['ns'] = $sys['cacher']['apc']['ns'];

        $sys['cacher']['redis']['connect'] = ['127.0.0.1', 6379, 2.5];

        $sys['cacher']['redis']['options'] = null;

        // }}}
        // {{{ templater

        /* Default templater.
         *
         * string 'Native' or 'Xslt'
         */
        $sys['templater']['default'] = 'Native';

        /* Native
         */
        $sys['templater']['native']['dir'] = APP_DIR . '/templates';

        $sys['templater']['native']['minify'] = true;

        /* Xslt
         */
        $sys['templater']['xslt']['dir'] = APP_DIR . '/templates';

        $sys['templater']['xslt']['root'] = 'root';

        $sys['templater']['xslt']['item'] = 'item';

        // }}}
        // {{{ notifier

        /* Enable notifier.
         *
         * Build() method in Notify classes will be called even if notifier disabled.
         *
         * bool
         */
        $sys['notifier']['enabled'] = true;

        /* Instead of disabling, you can override recipients by your email.
         *
         * ?array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
         */
        $sys['notifier']['recipients'] = null;

        /* Default sender.
         *
         * 'EMAIL' or array('EMAIL'[, 'NAME']) or null
         */
        $sys['notifier']['sender'] = null;

        /* Default replies.
         *
         * array('EMAIL' or array('EMAIL'[, 'NAME']), ...)
         */
        $sys['notifier']['replies'] = [];

        // }}}
        // {{{ locker

        /* Lock files pattern.
         *
         * string
         */
        $sys['locker']['file'] = APP_DIR . '/var/locks/{KEY}.lock';

        // }}}
        // {{{ logger

        /* Additional errors log file.
         *
         * ?string
         */
        $sys['logger']['file'] = APP_DIR . '/var/log/errors.log';

        // }}}
        // {{{ response

        /* Optional error documents pattern.
         *
         * ?string
         */
        $sys['response']['error_document'] = APP_DIR . '/public/.bin/errors/{CODE}.php';

        /* Pattern for statistics to page generated by templater.
         *
         * ?string
         */
        $sys['response']['stats'] = '<!-- script {SCR_T} + sql({SQL_C}) {SQL_T} + template({TPL_C}) {TPL_T} = {ALL_T} -->';

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
        // {{{ merger

        /* Sources to merge (sources are absolute, targets are just filenames).
         *
         * Null value disables merger.
         *
         * ?array
         */
        $sys['merger']['sources'] = null;

        /* URL location with merged JS and CSS files.
         *
         * string
         */
        $sys['merger']['location'] = '/.merged';

        /* Directory for merged JS and CSS files.
         *
         * string
         */
        $sys['merger']['dir'] = APP_DIR . '/var/cache/merged';

        /* Cache file with merger internal data.
         *
         * string
         */
        $sys['merger']['cache'] = APP_DIR . '/var/cache/merger.php';

        // }}}
        // {{{ router

        /* Cache file with router internal data.
         *
         * string
         */
        $sys['router']['cache'] = APP_DIR . '/var/cache/router.php';

        // }}}
        // {{{ paginator

        /* Name of page parameter in url for paginator.
         *
         * string
         */
        $sys['paginator']['param'] = 'i';

        // }}}

        return $sys;
    }
}
