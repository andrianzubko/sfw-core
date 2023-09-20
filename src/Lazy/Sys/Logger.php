<?php

namespace SFW\Lazy\Sys;

use Psr\Log\{LoggerInterface, LogLevel};

/**
 * Logger.
 */
class Logger extends \SFW\Lazy\Sys implements LoggerInterface
{
    /**
     * System is unusable.
     */
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     */
    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     */
    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     */
    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     */
    public function log(mixed $level, string|\Stringable $message, array $context = []): void
    {
        set_error_handler(fn() => true);

        if (!isset($level)
            || !is_string($level)
        ) {
            $level = LogLevel::ERROR;
        }

        if ($message instanceof \Throwable) {
            $message = (string) $message;
        } elseif (
            $context['append_file_and_line'] ?? true
        ) {
            if (isset($context['file'], $context['line'])) {
                $message = sprintf('%s in %s:%s',
                    $message,
                    $context['file'],
                    $context['line']
                );
            } elseif (
                isset($context['trace'])
            ) {
                $message = sprintf('%s in %s:%s',
                    $message,
                    $context['trace'][0]['file'],
                    $context['trace'][0]['line']
                );
            } else {
                foreach (debug_backtrace(2) as $item) {
                    if ($item['file'] !== __FILE__) {
                        $message = sprintf('%s in %s:%s',
                            $message,
                            $item['file'],
                            $item['line']
                        );

                        break;
                    }
                }
            }
        }

        $message = sprintf("[SFW %s] %s", ucfirst($level), $message);

        $context['timezone'] ??= self::$config['sys']['timezone'];

        $tzPrev = date_default_timezone_get();

        if ($tzPrev === $context['timezone']
            || !date_default_timezone_set($context['timezone'])
        ) {
            $tzPrev = null;
        }

        if (!isset($context['destination'])) {
            error_log($message);

            if (isset(self::$config['sys']['logger']['file'])) {
                $context['destination'] = self::$config['sys']['logger']['file'];
            }
        }

        if (isset($context['destination'])) {
            $this->sys('File')->put($context['destination'],
                sprintf("[%s] %s\n",
                    date('d-M-Y H:i:s e'), $message
                ), FILE_APPEND
            );
        }

        if (isset($tzPrev)) {
            date_default_timezone_set($tzPrev);
        }

        restore_error_handler();
    }

    /**
     * Logs database slow query.
     */
    public function logDbSlowQuery(float $timer, array $queries): void
    {
        if (!isset(self::$config['sys']['db']['slow_queries_log'])
            || $timer < self::$config['sys']['db']['slow_queries_min']
        ) {
            return;
        }

        $message = sprintf("[%.2f] %s\n\t%s\n",
            $timer,

            idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'],

            implode("\n\t",
                array_map(
                    fn($a) => $this->sys('Text')->fTrim($a), $queries
                )
            )
        );

        $this->info($message,
            [
                'destination' => self::$config['sys']['db']['slow_queries_log'],

                'append_file_and_line' => false,
            ]
        );
    }

    /**
     * Logs transactions fails.
     */
    public function logTransactionFail(string $level, string $state, int $retry): void
    {
        if (!isset(self::$config['sys']['transaction']['fails_log'])) {
            return;
        }

        $message = sprintf("[%s] [%d] %s",
            $state,
            $retry,

            idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI']
        );

        $this->log($level, $message,
            [
                'destination' => self::$config['sys']['transaction']['fails_log'],

                'append_file_and_line' => false,
            ]
        );
    }
}
