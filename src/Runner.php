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
            // {{{ configs

            self::$config['sys'] = \App\Config\Sys::get();

            self::$config['my'] = \App\Config\My::get();

            self::$config['shared'] = \App\Config\Shared::get();

            // }}}
            // {{{ custom error handler

            set_error_handler($this->errorHandler(...));

            // }}}
            // {{{ default timezone

            if (!date_default_timezone_set(self::$config['sys']['timezone'])) {
                throw new Exception\BadConfiguration(
                    sprintf(
                        'Unable to set timezone %s',
                            self::$config['sys']['timezone']
                    )
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
            // {{{ some important cleanups

            Utility::cleanup();

            // }}}
        } catch (\Throwable $e) {
            // {{{ something wrong

            self::sys('Notifier')->removeAll();

            self::sys('Shutdown')->unregisterAll();

            self::sys('Logger')->error($e);

            self::sys('Logger')->emergency('Application terminated!', [
                'append_file_and_line' => false
            ]);

            if (PHP_SAPI === 'cli') {
                $this->exit(1);
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

        $_SERVER['HTTP_SCHEME'] = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'
            ? 'http' : 'https';

        $_SERVER['REMOTE_ADDR'] ??= '0.0.0.0';

        $_SERVER['REQUEST_METHOD'] ??= 'GET';

        $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/';

        $chunks = explode('?', $_SERVER['REQUEST_URI'], 2);

        $_SERVER['REQUEST_PATH'] = $chunks[0];

        $_SERVER['QUERY_STRING'] = $chunks[1] ?? '';
    }

    /**
     * Custom error handler.
     *
     * @throws Exception\Logic
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
                    throw (new Exception\Logic($message))
                        ->setFile($file)
                        ->setLine($line);
            }
        }

        return true;
    }

    /**
     * Initializes system environment.
     *
     * @throws Exception\BadConfiguration
     */
    private function sysEnvironment(): void
    {
        self::$sys['timestamp'] = (int) self::$startedTime;

        if (isset(self::$config['sys']['url'])) {
            $url = parse_url(self::$config['sys']['url']);

            if (empty($url) || !isset($url['host'])) {
                throw new Exception\BadConfiguration('Incorrect url in system config');
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
            self::$sys['merged'] = Merger::process();
        }
    }

    /**
     * Initializes your environment (self::$my).
     */
    abstract protected function myEnvironment(): void;
}
