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
        // {{{ prevent multiple initizlizations

        if (isset(self::$globalMicrotime)) {
            return;
        }

        // }}}
        // {{{ fix microtime

        self::$globalMicrotime = gettimeofday(true);

        // }}}
        // {{{ checking inportant constants

        if (!defined('APP_DIR')) {
            $this->sys('Abend')->error('Undefined constant APP_DIR');
        }

        if (!defined('PUB_DIR')) {
            $this->sys('Abend')->error('Undefined constant PUB_DIR');
        }

        // }}}
        // {{{ config

        self::$config['sys'] = \App\Config\Sys::get();

        self::$config['my'] = \App\Config\My::get();

        self::$config['shared'] = \App\Config\Shared::get();

        self::$e['config'] = &self::$config['shared'];

        // }}}
        // {{{ default locale, encoding and timezone

        setlocale(LC_ALL, 'C');

        mb_internal_encoding('UTF-8');

        if (date_default_timezone_set(self::$config['sys']['timezone']) === false) {
            $this->sys('Abend')->error();
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

        if (isset(self::$config['sys']['url'])) {
            $parsed = parse_url(self::$config['sys']['url']);

            if (!isset($parsed['host'])) {
                $this->sys('Abend')->error('Incorrect url in system config');
            }

            self::$e['defaults']['url_scheme'] = $parsed['scheme'] ?? 'http';

            self::$e['defaults']['url_host'] = $parsed['host'];
        } else {
            self::$e['defaults']['url_scheme'] =
                empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'
                    ? 'http' : 'https';

            self::$e['defaults']['url_host'] = $_SERVER['HTTP_HOST'];
        }

        self::$e['defaults']['url'] = sprintf('%s://%s',
            self::$e['defaults']['url_scheme'],
            self::$e['defaults']['url_host']
        );

        self::$e['defaults']['timestamp'] = (int) self::$globalMicrotime;

        self::$e['defaults']['point'] = (new \App\Router())->get();

        if (self::$e['defaults']['point'] === false) {
            $this->sys('Abend')->errorPage(404);
        }

        // }}}
        // {{{ additional environment

        $this->environment();

        // }}}
        // {{{ calling entry point if this is not CLI

        if (php_sapi_name() === 'cli') {
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
