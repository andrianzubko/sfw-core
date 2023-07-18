<?php

namespace SFW;

/**
 * Simplest framework runner.
 */
abstract class Runner extends Base
{
    /**
     * Initializing environment and calling entry point.
     */
    final public function __construct()
    {
        // {{{ prevent multiple initializations

        if (isset(self::$startedTime)) {
            return;
        }

        // }}}
        // {{{ started time

        self::$startedTime = gettimeofday(true);

        // }}}
        // {{{ checking important constant

        if (!defined('APP_DIR')) {
            $this->sys('Abend')->error('Undefined constant APP_DIR');
        }

        // }}}
        // {{{ configs

        self::$config['sys'] = \App\Config\Sys::get();

        self::$config['my'] = \App\Config\My::get();

        self::$config['shared'] = \App\Config\Shared::get();

        self::$e['config'] = &self::$config['shared'];

        // }}}
        // {{{ default locale

        setlocale(LC_ALL, 'C');

        // }}}
        // {{{ default encoding

        mb_internal_encoding('UTF-8');

        // }}}
        // {{{ default timezone

        if (date_default_timezone_set(self::$config['sys']['timezone']) === false) {
            $this->sys('Abend')->error();
        }

        // }}}
        // {{{ some parameters correcting

        $_SERVER['HTTP_HOST'] ??= 'localhost';

        $_SERVER['REMOTE_ADDR'] ??= '0.0.0.0';

        $_SERVER['REQUEST_METHOD'] ??= 'GET';

        $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/';

        $chunks = explode('?', $_SERVER['REQUEST_URI'], 2);

        $_SERVER['REQUEST_URL'] = $chunks[0];

        $_SERVER['REQUEST_QUERY'] = $chunks[1] ?? '';

        // }}}
        // {{{ default environment

        if (isset(self::$config['sys']['url'])) {
            $parsed = parse_url(self::$config['sys']['url']);

            if (!isset($parsed['host'])) {
                $this->sys('Abend')->error('Incorrect url in system config');
            }

            self::$e['defaults']['url_scheme'] = $parsed['scheme'] ?? 'http';

            self::$e['defaults']['url_host'] = $parsed['host'];
        } else {
            if (empty($_SERVER['HTTPS'])
                || $_SERVER['HTTPS'] === 'off'
            ) {
                self::$e['defaults']['url_scheme'] = 'http';
            } else {
                self::$e['defaults']['url_scheme'] = 'https';
            }

            self::$e['defaults']['url_host'] = $_SERVER['HTTP_HOST'];
        }

        self::$e['defaults']['url'] = sprintf('%s://%s',
            self::$e['defaults']['url_scheme'],
            self::$e['defaults']['url_host']
        );

        self::$e['defaults']['timestamp'] = (int) self::$startedTime;

        self::$e['defaults']['point'] = (new \App\Router())->get();

        if (self::$e['defaults']['point'] === false) {
            $this->sys('Abend')->errorPage(404);
        }

        // }}}
        // {{{ additional environment

        $this->environment();

        // }}}
        // {{{ calling entry point if this is not CLI

        if (PHP_SAPI === 'cli') {
            return;
        }

        $point = self::$e['defaults']['point'];

        $class = "App\\Point\\$point";

        if (!class_exists($class)) {
            $this->sys('Abend')->errorPage(404);
        }

        try {
            new $class();
        } catch (\Exception $error) {
            $this->sys('Abend')->error(
                $error->getMessage(),
                $error->getFile(),
                $error->getLine()
            );
        }

        // }}}
    }

    /**
     * Initializing additional environment.
     */
    abstract protected function environment(): void;
}
