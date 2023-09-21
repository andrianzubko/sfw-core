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

            ini_set('error_reporting', E_ALL);

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
            // {{{ custom error handler

            set_error_handler($this->errorHandler(...));

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
            // {{{ server parameters correcting

            $this->correctServerParams();

            // }}}
            // {{{ initializing default environment

            $this->defaultEnvironment();

            // }}}
            // {{{ initializing additional environment

            $this->additionalEnvironment();

            // }}}
            // {{{ calling Command or Controller request handler

            [$class, $method, self::$e['sys']['action']] = (new Router())->get();

            if ($class === false
                || !method_exists($class, $method)
            ) {
                if (PHP_SAPI === 'cli') {
                    $this->exit();
                } else {
                    $this->sys('Response')->errorPage(404);
                }
            }

            $handler = new $class();

            if ($method !== '__construct') {
                $handler->$method();
            }

            // }}}
        } catch (\Throwable $error) {
            // {{{ something wrong

            $this->sys('Notifier')->removeAll();

            $this->sys('Shutdown')->unregisterAll();

            $this->sys('Logger')->error($error);

            $this->sys('Logger')->emergency('Application terminated!', [
                'append_file_and_line' => false
            ]);

            if (PHP_SAPI === 'cli') {
                $this->exit(1);
            } else {
                $this->sys('Response')->errorPage(500);
            }

            // }}}
        }
    }

    /**
     * Custom error handler.
     *
     * @throws LogicException
     */
    private function errorHandler(int $code, string $message, string $file, int $line): bool
    {
        if (error_reporting() & $code) {
            switch ($code) {
                case E_NOTICE:
                case E_USER_NOTICE:
                    $this->sys('Logger')->notice($message, [
                        'file' => $file,
                        'line' => $line
                    ]);
                    break;
                case E_WARNING:
                case E_USER_WARNING:
                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                case E_STRICT:
                    $this->sys('Logger')->warning($message, [
                        'file' => $file,
                        'line' => $line
                    ]);
                    break;
                default:
                    throw (new LogicException($message))
                        ->setFile($file)
                        ->setLine($line);
            }
        }

        return true;
    }

    /**
     * Corrects server parameters.
     */
    private function correctServerParams(): void
    {
        $_SERVER['HTTP_HOST'] ??= 'localhost';

        $_SERVER['HTTP_SCHEME'] = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'
            ? 'http' : 'https';

        $_SERVER['REMOTE_ADDR'] ??= '0.0.0.0';

        $_SERVER['REQUEST_METHOD'] ??= 'GET';

        $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/';

        [
            $_SERVER['REQUEST_URL'],
            $_SERVER['REQUEST_QUERY']
        ] = array_pad(explode('?', $_SERVER['REQUEST_URI'], 2), 2, '');
    }

    /**
     * Initializing default environment.
     *
     * @throws BadConfigurationException
     */
    private function defaultEnvironment(): void
    {
        self::$e['sys']['timestamp'] = (int) self::$startedTime;

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
            self::$e['sys']['url_scheme'], self::$e['sys']['url_host']
        );
    }

    /**
     * Initializing additional environment.
     */
    abstract protected function additionalEnvironment(): void;
}
