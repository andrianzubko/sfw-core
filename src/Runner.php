<?php

declare(strict_types=1);

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
                throw new Exception\BadConfiguration('Unable to set timezone ' . self::$sys['config']['timezone']);
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
                    throw new Exception\BadConfiguration('Incorrect url in system configuration');
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
                self::$sys['action'] = (new Router\Command())->getCurrentAction();

                if (self::$sys['action'] === false) {
                    throw new Exception\UnexpectedValue('Command not found');
                }
            } else {
                self::$sys['action'] = (new Router\Controller())->getCurrentAction();

                if (self::$sys['action'] === false) {
                    self::sys('Response')->error(404);
                }
            }

            // }}}
            // {{{ your configuration

            self::$my['config'] = \App\Config\My::init();

            // }}}
            // {{{ merging CSS and JS

            if (self::$sys['config']['merger_sources'] !== null && PHP_SAPI !== 'cli') {
                self::$sys['merged'] = (new Merger())->merge();
            }

            // }}}
            // {{{ cleanups and dispatching event at shutdown

            register_shutdown_function($this->cleanupAndDispatchAtShutdown(...));

            // }}}
            // {{{ your environment

            $this->environment();

            // }}}
            // {{{ calling Command or Controller action

            if (self::$sys['action'] !== null) {
                Callback::normalize(self::$sys['action']['full'])();
            }

            // }}}
        } catch (\Throwable $e) {
            // {{{ something wrong

            self::sys('Provider')->removeAllListeners(true);

            self::sys('Logger')->error($e);

            self::sys('Logger')->emergency('Application terminated!', options: [
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
                    self::sys('Logger')->notice($message, options: [
                        'file' => $file,
                        'line' => $line
                    ]);
                    break;
                case E_WARNING:
                case E_USER_WARNING:
                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                case E_STRICT:
                    self::sys('Logger')->warning($message, options: [
                        'file' => $file,
                        'line' => $line
                    ]);
                    break;
                default:
                    throw (new Exception\Logic($message))->setFile($file)->setLine($line);
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
                } catch (\SFW\Databaser\Exception) {}
            }
        }

        self::sys('Dispatcher')->dispatch(new Event\Shutdown(), true);
    }

    /**
     * Initializes your environment.
     */
    abstract protected function environment(): void;
}
