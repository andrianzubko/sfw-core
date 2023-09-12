<?php

namespace SFW;

/**
 * Simplest framework runner.
 */
abstract class Runner extends Base
{
    /**
     * Initializing environment and calling Controller class.
     */
    final public function __construct()
    {
        try {
            // {{{ prevent multiple initializations

            if (isset(self::$startedTime)) {
                return;
            }

            // }}}
            // {{{ started time

            self::$startedTime = gettimeofday(true);

            // }}}
            // {{{ application dir

            define('APP_DIR', dirname(__DIR__, 4));

            // }}}
            // {{{ important PHP params.

            ini_set('display_errors', PHP_SAPI === 'cli');

            ini_set('error_reporting', -1);

            ini_set('ignore_user_abort', true);

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

            if (!date_default_timezone_set(self::$config['sys']['timezone'])) {
                throw new BadConfigurationException(
                    sprintf(
                        'Unable to set timezone %s',
                            self::$config['sys']['timezone']
                    )
                );
            }

            // }}}
            // {{{ some parameters correcting

            $_SERVER['HTTP_HOST'] ??= 'localhost';

            $_SERVER['HTTP_SCHEME'] = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'
                ? 'http' : 'https';

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
                    throw new BadConfigurationException('Incorrect url in system config');
                }

                self::$e['sys']['url_scheme'] = $parsed['scheme'] ?? 'http';

                self::$e['sys']['url_host'] = $parsed['host'];
            } else {
                self::$e['sys']['url_scheme'] = $_SERVER['HTTP_SCHEME'];

                self::$e['sys']['url_host'] = $_SERVER['HTTP_HOST'];
            }

            self::$e['sys']['url'] = sprintf('%s://%s',
                self::$e['sys']['url_scheme'],
                self::$e['sys']['url_host']
            );

            self::$e['sys']['timestamp'] = (int) self::$startedTime;

            $controller = self::$e['sys']['controller'] = (new \App\Router())->get();

            if ($controller === false) {
                $this->sys('Response')->errorPage(404);
            }

            // }}}
            // {{{ additional environment

            $this->environment();

            // }}}
            // {{{ calling Controller class if not cli mode

            if (PHP_SAPI !== 'cli') {
                $class = "App\\Controller\\$controller";

                if (!class_exists($class)) {
                    $this->sys('Response')->errorPage(404);
                }

                new $class();
            } else {
                set_time_limit(0);
            }

            // }}}
        } catch (\Throwable $error) {
            // {{{ something wrong

            $this->sys('Logger')->error($error);

            $this->sys('Logger')->emergency('Application terminated!',
                ['append_file_and_line' => false]
            );

            $this->sys('Response')->errorPage(500);

            // }}}
        }
    }

    /**
     * Initializing additional environment.
     */
    abstract protected function environment(): void;
}
