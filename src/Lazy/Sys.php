<?php

namespace SFW\Lazy;

/**
 * Some system functionality.
 */
class Sys extends \SFW\Lazy
{
    /**
     * Setting default environment.
     */
    final public function setDefaultEnvironment(): void
    {
        // {{{ starting microtime

        self::$started = gettimeofday(true);

        // }}}
        // {{{ important parameters checking and correcting

        if (isset($_SERVER['REDIRECT_REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_REQUEST_URI'];
        }

        foreach (['DOCUMENT_ROOT', 'REQUEST_URI', 'REQUEST_METHOD', 'REMOTE_ADDR', 'HTTP_HOST'] as $name) {
            if (!isset($_SERVER[$name])) {
                $this->abend()->error("Server parameter $name must be defined!");
            }
        }

        [$_SERVER['REQUEST_URL'], $_SERVER['REQUEST_QUERY']] = [
            ...explode('?', $_SERVER['REQUEST_URI'], 2), ''
        ];

        // }}}
        // {{{ configs merging

        self::$config = array_merge(
            (array) new \SFW\Config\Sys(),
            (array) new \Config\Sys()
        );

        self::$e['config'] = array_merge(
            (array) new \SFW\Config\Extend(),
            (array) new \Config\Extend()
        );

        // }}}
        // {{{ default locale, encoding and timezone

        setlocale(LC_ALL, 'C');

        mb_internal_encoding('UTF-8');

        if (date_default_timezone_set(self::$e['config']['timezone']) === false) {
            $this->abend()->error();
        }

        // }}}
        // {{{ default environment

        if (isset($_REQUEST['REQUEST_URI']) &&
                is_scalar($_REQUEST['REQUEST_URI']) &&
                    mb_check_encoding($_REQUEST['REQUEST_URI'])) {

            self::$e['system']['requestUri'] = $_REQUEST['REQUEST_URI'];

            [self::$e['system']['requestUrl'], self::$e['system']['requestQuery']] = [
                ...explode('?', self::$e['system']['requestUri'], 2), ''
            ];
        } else {
            self::$e['system']['requestUri'] = $_SERVER['REQUEST_URI'];

            self::$e['system']['requestUrl'] = $_SERVER['REQUEST_URL'];

            self::$e['system']['requestQuery'] = $_SERVER['REQUEST_QUERY'];
        }

        self::$e['system']['requestMethod'] = $_SERVER['REQUEST_METHOD'];

        if (isset(self::$e['config']['basicUrl'])) {
            $parsed = parse_url(self::$e['config']['basicUrl']);

            if (!isset($parsed['host'])) {
                $this->abend()->error('Incorrect basicUrl in extended configuration');
            }

            self::$e['system']['basicUrlScheme'] = $parsed['scheme'] ?? 'http';

            self::$e['system']['basicUrlHost'] = $parsed['host'];
        } else {
            self::$e['system']['basicUrlScheme'] =
                empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'
                    ? 'http' : 'https';

            self::$e['system']['basicUrlHost'] = $_SERVER['HTTP_HOST'];
        }

        self::$e['system']['basicUrl'] = sprintf('%s://%s',
            self::$e['system']['basicUrlScheme'], self::$e['system']['basicUrlHost']
        );

        self::$e['system']['timestamp'] = (int) self::$started;

        if (self::$config['recombineCssAndJs']) {
            $this->merger()->recombine();
        }

        self::$e['system']['merged'] = $this->merger()->getTime();

        self::$e['system']['device'] = $this->detector()->device;

        self::$e['system']['os'] = $this->detector()->os;

        self::$e['system']['point'] = $this->router()->getPoint();

        // }}}
    }

    /**
     * Setting extra environment.
     */
    public function setExtraEnvironment(): void {}
}
