<?php

namespace SFW;

use SFW\Exception\{BadConfiguration, Logic};

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

            if (isset(self::$sys['started'])) {
                return;
            }

            // }}}
            // {{{ started time

            self::$sys['started'] = gettimeofday(true);

            // }}}
            // {{{ important PHP parameters.

            ini_set('display_errors', PHP_SAPI === 'cli');

            ini_set('error_reporting', E_ALL);

            ini_set('ignore_user_abort', true);

            // }}}
            // {{{ default locale

            setlocale(LC_ALL, 'C');

            // }}}
            // {{{ default encoding

            mb_internal_encoding('UTF-8');

            // }}}
            // {{{ server parameters correcting

            $this->correctServerParams();

            // }}}
            // {{{ application dir

            define('APP_DIR', dirname((new \ReflectionClass(static::class))->getFileName(), 2));

            // }}}
            // {{{ initializing system configuration

            self::$sys['config'] = \App\Config\Sys::init();

            // }}}
            // {{{ custom error handler

            set_error_handler($this->errorHandler(...));

            // }}}
            // {{{ default timezone

            if (!date_default_timezone_set(self::$sys['config']['timezone'])) {
                throw new BadConfiguration(
                    sprintf('Unable to set timezone %s', self::$sys['config']['timezone'])
                );
            }

            // }}}
            // {{{ routing

            if (PHP_SAPI === 'cli') {
                [$class, $method, self::$sys['action']] = Router\Command::getTarget();
            } else {
                [$class, $method, self::$sys['action']] = Router\Controller::getTarget();

                if ($class === false) {
                    self::sys('Response')->error(404);
                }
            }

            // }}}
            // {{{ initializing system environment

            $this->sysEnvironment();

            // }}}
            // {{{ registering cleanups and dispatch events at shutdown

            register_shutdown_function($this->cleanupAndDispatchEventsAtShutdown(...));

            // }}}
            // {{{ initializing your configuration

            self::$my['config'] = \App\Config\My::init();

            // }}}
            // {{{ initializing your environment

            $this->myEnvironment();

            // }}}
            // {{{ calling Command or Controller action

            if (PHP_SAPI === 'cli') {
                if ($class !== false) {
                    new $class();
                }
            } else {
                if (method_exists($class, $method)) {
                    $controller = new $class();

                    if ($method !== '__construct') {
                        $controller->$method();
                    }
                } else {
                    self::sys('Response')->error(404);
                }
            }

            // }}}
        } catch (\Throwable $e) {
            // {{{ something wrong

            self::sys('Provider')->removeAllListeners();

            self::sys('Logger')->error($e);

            self::sys('Logger')->emergency('Application terminated!', [
                'append_file_and_line' => false
            ]);

            if (PHP_SAPI === 'cli') {
                exit(1);
            } else {
                self::sys('Response')->error(500);
            }

            // }}}
        }
    }

    /**
     * Corrects server parameters.
     */
    private function correctServerParams(): void
    {
        $_SERVER['HTTP_HOST'] ??= 'localhost';

        $_SERVER['HTTP_SCHEME'] = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https';

        $_SERVER['REMOTE_ADDR'] ??= '0.0.0.0';

        $_SERVER['REQUEST_METHOD'] ??= 'GET';

        $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/';

        $chunks = explode('?', $_SERVER['REQUEST_URI'], 2);

        $_SERVER['REQUEST_PATH'] = $chunks[0];

        $_SERVER['QUERY_STRING'] = $chunks[1] ?? '';
    }

    /**
     * Initializes system environment.
     *
     * @throws BadConfiguration
     */
    private function sysEnvironment(): void
    {
        self::$sys['timestamp'] = (int) self::$sys['started'];

        if (self::$sys['config']['url'] === null) {
            self::$sys['url_scheme'] = $_SERVER['HTTP_SCHEME'];

            self::$sys['url_host'] = $_SERVER['HTTP_HOST'];
        } else {
            $url = parse_url(self::$sys['config']['url']);

            if (empty($url) || !isset($url['host'])) {
                throw new BadConfiguration('Incorrect url in system configuration');
            }

            self::$sys['url_scheme'] = $url['scheme'] ?? 'http';

            if (isset($url['port'])) {
                self::$sys['url_host'] = $url['host'] . ':' . $url['port'];
            } else {
                self::$sys['url_host'] = $url['host'];
            }
        }

        self::$sys['url'] = self::$sys['url_scheme'] . '://' . self::$sys['url_host'];

        if (PHP_SAPI !== 'cli'
            && self::$sys['config']['merger_sources'] !== null
        ) {
            self::$sys['merged'] = Merger::process();
        }
    }

    /**
     * Custom error handler.
     *
     * @throws Logic
     */
    private function errorHandler(int $code, string $message, string $file, int $line): bool
    {
        if (error_reporting() & $code) {
            switch ($code) {
                case E_NOTICE:
                case E_USER_NOTICE:
                    self::sys('Logger')->notice($message, [
                        'file' => $file,
                        'line' => $line
                    ]);
                    break;
                case E_WARNING:
                case E_USER_WARNING:
                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                case E_STRICT:
                    self::sys('Logger')->warning($message, [
                        'file' => $file,
                        'line' => $line
                    ]);
                    break;
                default:
                    throw (new Logic($message))->setFile($file)->setLine($line);
            }
        }

        return true;
    }

    /**
     * Cleanups and dispatch events at shutdown.
     */
    private function cleanupAndDispatchEventsAtShutdown(): void
    {
        unset(self::$sysLazies['Db']);

        foreach (self::$sysLazies as $lazy) {
            if ($lazy instanceof \SFW\Databaser\Driver && $lazy->isInTrans()) {
                try {
                    $lazy->rollback();
                } catch (\SFW\Databaser\Exception) {
                }
            }
        }

        self::sys('Dispatcher')->dispatch(new Event\Shutdown(), true);
    }

    /**
     * Initializes your environment.
     */
    abstract protected function myEnvironment(): void;
}
