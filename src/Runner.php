<?php

namespace SFW;

/**
 * Simplest framework runner.
 */
abstract class Runner extends Base
{
    /**
     * Initizlizing environment and calling entry point.
     */
    final public function __construct()
    {
        // {{{ prevent double initizlization and fix microtime

        if (isset(self::$globalMicrotime)) {
            return;
        }

        self::$globalMicrotime = gettimeofday(true);

        // }}}
        // {{{ configs

        self::$config['sys'] = new \App\Config\Sys();

        self::$config['my'] = new \App\Config\My();

        // }}}
        // {{{ callers of Lazy classes

        self::$sys = new Lazy\SysCaller();

        self::$my = new Lazy\MyCaller();

        // }}}
        // {{{ default locale, encoding and timezone

        setlocale(LC_ALL, 'C');

        mb_internal_encoding('UTF-8');

        if (date_default_timezone_set(self::$config['sys']->timezone) === false) {
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

        self::$e['config'] = new \App\Config\Defaults();

        if (isset(self::$config['sys']->basicUrl)) {
            $parsed = parse_url(self::$config['sys']->basicUrl);

            if (!isset($parsed['host'])) {
                self::$sys->abend()->error('Incorrect basicUrl in Sys config');
            }

            self::$e['defaults']['basic_url_scheme'] = $parsed['scheme'] ?? 'http';

            self::$e['defaults']['basic_url_host'] = $parsed['host'];
        } else {
            self::$e['defaults']['basic_url_scheme'] =
                empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'
                    ? 'http' : 'https';

            self::$e['defaults']['basic_url_host'] = $_SERVER['HTTP_HOST'];
        }

        self::$e['defaults']['basic_url'] = sprintf('%s://%s',
            self::$e['defaults']['basic_url_scheme'],
            self::$e['defaults']['basic_url_host']
        );

        self::$e['defaults']['timestamp'] = (int) self::$globalMicrotime;

        self::$e['defaults']['point'] = (new \App\Router())->get();

        if (self::$e['defaults']['point'] === false) {
            self::$sys->abend()->errorPage(404);
        }

        // }}}
        // {{{ additional environment

        $this->environment();

        // }}}
        // {{{ calling entry point if this is not test suite

        if ($_SERVER['REMOTE_ADDR'] === '0.0.0.0') {
            return;
        }

        $class = 'App\\Point\\' . self::$e['defaults']['point'];

        if (!class_exists($class)) {
            self::$sys->abend()->errorPage(404);
        }

        new $class();

        // }}}
    }

    /**
     * Initializing additional environment.
     */
    abstract protected function environment(): void;
}
