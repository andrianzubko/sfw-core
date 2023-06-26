<?php

namespace SFW;

/**
 * Simplest framework runner.
 */
abstract class Runner extends Base
{
    /**
     * Initializing environment and routing to starting point.
     */
    final public function __construct()
    {
        // {{{ getting global microtime and preventing multiple initialization

        if (isset(self::$globalMicrotime)) {
            return;
        }

        self::$globalMicrotime = gettimeofday(true);

        // }}}
        // {{{ lazy classes callers

        self::$sys = new \SFW\Lazy\SysCaller();

        self::$my = new \SFW\Lazy\MyCaller();

        // }}}
        // {{{ configs merging as arrays

        self::$config = array_merge(
            (array) new \SFW\Config\Sys(),
            (array) new \App\Config\Sys()
        );

        self::$e['config'] = array_merge(
            (array) new \SFW\Config\Extend(),
            (array) new \App\Config\Extend()
        );

        // }}}
        // {{{ default locale, encoding and timezone

        setlocale(LC_ALL, 'C');

        mb_internal_encoding('UTF-8');

        if (date_default_timezone_set(self::$e['config']['timezone']) === false) {
            self::$sys->abend()->error();
        }

        // }}}
        // {{{ important parameters checking and correcting

        $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/';

        $_SERVER['REQUEST_METHOD'] ??= 'GET';

        $_SERVER['REMOTE_ADDR'] ??= '0.0.0.0';

        $_SERVER['HTTP_HOST'] ??= 'localhost';

        [$_SERVER['REQUEST_URL'], $_SERVER['REQUEST_QUERY']] = [
            ...explode('?', $_SERVER['REQUEST_URI'], 2), ''
        ];

        // }}}
        // {{{ default environment

        if (isset(self::$e['config']['basic_url'])) {
            $parsed = parse_url(self::$e['config']['basic_url']);

            if (!isset($parsed['host'])) {
                self::$sys->abend()->error('Incorrect basicUrl in extended configuration');
            }

            self::$e['system']['basic_url_scheme'] = $parsed['scheme'] ?? 'http';

            self::$e['system']['basic_url_host'] = $parsed['host'];
        } else {
            self::$e['system']['basic_url_scheme'] =
                empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'
                    ? 'http' : 'https';

            self::$e['system']['basic_url_host'] = $_SERVER['HTTP_HOST'];
        }

        self::$e['system']['basic_url'] = sprintf('%s://%s',
            self::$e['system']['basic_url_scheme'],
            self::$e['system']['basic_url_host']
        );

        self::$e['system']['timestamp'] = (int) self::$globalMicrotime;

        self::$e['system']['point'] = (new \App\Router())->get();

        if (self::$e['system']['point'] === false) {
            self::$sys->abend()->errorPage(404);
        }

        // }}}
        // {{{ additional environment

        $this->environment();

        // }}}
        // {{{ go to routed enty point if runned not under test suite.

        if ($_SERVER['REMOTE_ADDR'] !== '0.0.0.0') {
            $point = self::$e['system']['point'];

            if (!class_exists("\\App\\Point\\$point")) {
                self::$sys->abend()->errorPage(404);
            }

            new ("\\App\\Point\\$point")();
        }

        // }}}
    }

    /**
     * For additional environment.
     */
    abstract protected function environment(): void;
}
