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
        // {{{ prevent multiple initializations

        if (self::$sys) {
            return;
        }

        // }}}
        // {{{ started time

        self::$sys['timestamp_float'] = gettimeofday(true);

        self::$sys['timestamp'] = (int) self::$sys['timestamp_float'];

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
        // {{{ application dir

        define('APP_DIR', dirname((new \ReflectionClass(static::class))->getFileName(), 2));

        // }}}
        // {{{ some server parameters correcting

        $_SERVER['HTTP_HOST'] ??= 'localhost';

        $_SERVER['HTTP_SCHEME'] = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https';

        $_SERVER['REMOTE_ADDR'] ??= '0.0.0.0';

        $_SERVER['REQUEST_METHOD'] ??= 'GET';

        $_SERVER['REQUEST_URI'] = $_SERVER['REDIRECT_REQUEST_URI'] ?? $_SERVER['REQUEST_URI'] ?? '/';

        $chunks = explode('?', $_SERVER['REQUEST_URI'], 2);

        $_SERVER['REQUEST_PATH'] = $chunks[0];

        $_SERVER['QUERY_STRING'] = $chunks[1] ?? '';

        // }}}
        // {{{ system configuration

        self::$sys['config'] = \App\Config\Sys::init();

        // }}}
        // {{{ custom error handler

        set_error_handler($this->errorHandler(...));

        // }}}

        try {
            // {{{ default timezone

            if (!date_default_timezone_set(self::$sys['config']['timezone'])) {
                throw new BadConfiguration(
                    sprintf('Unable to set timezone %s', self::$sys['config']['timezone'])
                );
            }

            // }}}
            // {{{ basic url and redirect if needed

            if (self::$sys['config']['url'] === null) {
                self::$sys['url_scheme'] = $_SERVER['HTTP_SCHEME'];

                self::$sys['url_host'] = $_SERVER['HTTP_HOST'];

                self::$sys['url'] = self::$sys['url_scheme'] . '://' . self::$sys['url_host'];
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

                self::$sys['url'] = self::$sys['url_scheme'] . '://' . self::$sys['url_host'];

                if (self::$sys['url_scheme'] !== $_SERVER['HTTP_SCHEME']
                    || self::$sys['url_host'] !== $_SERVER['HTTP_HOST']
                ) {
                    self::sys('Response')->redirect(self::$sys['url'] . $_SERVER['REQUEST_URI']);
                }
            }

            // }}}
            // {{{ routing

            if (PHP_SAPI === 'cli') {
                $target = (new Router\Command())->getTarget();
            } else {
                $target = (new Router\Controller())->getTarget();

                if ($target === false) {
                    self::sys('Response')->error(404);
                }
            }

            self::$sys['action'] = $target ? $target->action : null;

            // }}}
            // {{{ your configuration

            self::$my['config'] = \App\Config\My::init();

            // }}}
            // {{{ merging CSS and JS

            if (self::$sys['config']['merger_sources'] !== null && PHP_SAPI !== 'cli') {
                self::$sys['merged'] = (new Merger())->process();
            }

            // }}}
            // {{{ cleanups and dispatching event at shutdown

            register_shutdown_function($this->cleanupAndDispatchAtShutdown(...));

            // }}}
            // {{{ your environment

            $this->environment();

            // }}}
            // {{{ calling Command or Controller action

            if (PHP_SAPI === 'cli') {
                if ($target !== false) {
                    new ($target->class)();
                }
            } elseif (method_exists($target->class, $target->method)) {
                $controller = new ($target->class)();

                if ($target->method !== '__construct') {
                    $controller->{$target->method}();
                }
            } else {
                self::sys('Response')->error(404);
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
     * Custom error handler.
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
     * Cleanups and dispatching event at shutdown.
     */
    private function cleanupAndDispatchAtShutdown(): void
    {
        $sysLazies = (new \ReflectionClass(Base::class))->getStaticPropertyValue('sysLazies');

        foreach ($sysLazies as $name => $lazy) {
            if ($lazy instanceof \SFW\Databaser\Driver && $name !== 'Db' && $lazy->isInTrans()) {
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
    abstract protected function environment(): void;
}
