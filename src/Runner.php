<?php

namespace SFW;

/**
 * Simplest framework runner.
 */
abstract class Runner extends Base
{
    /**
     * Initializes environment and calls Controller or Command class.
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

            define('APP_DIR',
                dirname((new \ReflectionClass(static::class))->getFileName(), 2)
            );

            // }}}
            // {{{ important PHP parameters.

            ini_set('display_errors', PHP_SAPI === 'cli');

            ini_set('error_reporting', E_ALL);

            ini_set('ignore_user_abort', true);

            // }}}
            // {{{ configs

            self::$config['sys'] = \App\Config\Sys::get();

            self::$config['my'] = \App\Config\My::get();

            self::$config['shared'] = \App\Config\Shared::get();

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
            // {{{ cleanup after dirty exit from transaction

            register_shutdown_function(
                function () {
                    if (isset(self::$sysLazies['Db'])) {
                        self::$sysLazies['Db'] = $this->sys(self::$config['sys']['db']['default']);
                    }
                }
            );

            // }}}
            // {{{ initializing additional environment

            $this->additionalEnvironment();

            // }}}
            // {{{ calling Command or Controller action

            if (PHP_SAPI === 'cli') {
                $router = new \SFW\Router\Command();
            } else {
                $router = new \SFW\Router\Controller();
            }

            [self::$sys['action'], $class, $method] = $router->getAction();

            if (self::$sys['action'] !== false
                && (method_exists($class, $method)
                    || PHP_SAPI === 'cli')
            ) {
                $instance = new $class();

                if ($method !== '__construct') {
                    $instance->$method();
                }
            } elseif (PHP_SAPI !== 'cli') {
                $this->sys('Response')->errorPage(404);
            }

            // }}}
        } catch (\Throwable $e) {
            // {{{ something wrong

            $this->sys('Notifier')->removeAll();

            $this->sys('Shutdown')->unregisterAll();

            $this->sys('Logger')->error($e);

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

        $chunks = explode('?', $_SERVER['REQUEST_URI'], 2);

        $_SERVER['REQUEST_URL'] = $chunks[0];

        $_SERVER['REQUEST_QUERY'] = $chunks[1] ?? '';
    }

    /**
     * Initializes default environment.
     *
     * @throws BadConfigurationException
     */
    private function defaultEnvironment(): void
    {
        self::$sys['timestamp'] = (int) self::$startedTime;

        if (isset(self::$config['sys']['url'])) {
            $url = parse_url(self::$config['sys']['url']);

            if (empty($url)
                || !isset($url['host'])
            ) {
                throw new BadConfigurationException('Incorrect url in system config');
            }

            self::$sys['url_scheme'] = $url['scheme'] ?? 'http';

            if (isset($url['port'])) {
                self::$sys['url_host'] = $url['host'] . ':' . $url['port'];
            } else {
                self::$sys['url_host'] = $url['host'];
            }
        } else {
            self::$sys['url_scheme'] = $_SERVER['HTTP_SCHEME'];

            self::$sys['url_host'] = $_SERVER['HTTP_HOST'];
        }

        self::$sys['url'] = self::$sys['url_scheme'] . '://' . self::$sys['url_host'];

        if (isset(self::$config['sys']['merger']['sources'])
            && PHP_SAPI !== 'cli'
        ) {
            self::$sys['merged'] = (new Merger())->process();
        }
    }

    /**
     * Initializes additional environment.
     */
    abstract protected function additionalEnvironment(): void;
}
