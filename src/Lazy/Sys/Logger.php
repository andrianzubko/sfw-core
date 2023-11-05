<?php

namespace SFW\Lazy\Sys;

use Psr\Log\{LoggerInterface, LogLevel};

/**
 * Logger.
 */
class Logger extends \SFW\Lazy\Sys implements LoggerInterface
{
    /**
     * Timezone.
     */
    protected string $timezone;

    /**
     * Sets default timezone.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    public function __construct()
    {
        $this->setTimezone(self::$sys['config']['timezone']);
    }

    /**
     * System is unusable.
     */
    public function emergency(string|\Stringable $message, array $context = [], array $options = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context, $options);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     */
    public function alert(string|\Stringable $message, array $context = [], array $options = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context, $options);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public function critical(string|\Stringable $message, array $context = [], array $options = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context, $options);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public function error(string|\Stringable $message, array $context = [], array $options = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context, $options);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    public function warning(string|\Stringable $message, array $context = [], array $options = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context, $options);
    }

    /**
     * Normal but significant events.
     */
    public function notice(string|\Stringable $message, array $context = [], array $options = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context, $options);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    public function info(string|\Stringable $message, array $context = [], array $options = []): void
    {
        $this->log(LogLevel::INFO, $message, $context, $options);
    }

    /**
     * Detailed debug information.
     */
    public function debug(string|\Stringable $message, array $context = [], array $options = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context, $options);
    }

    /**
     * Logs with an arbitrary level.
     */
    public function log(mixed $level, string|\Stringable $message, array $context = [], array $options = []): void
    {
        set_error_handler(fn() => true);

        if (!\is_string($level)) {
            $level = LogLevel::ERROR;
        }

        if ($message instanceof \Throwable) {
            $message = (string) $message;
        } else {
            if ($context !== null) {
                $message .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
            }

            if ($options['append_file_and_line'] ?? true) {
                if (isset($options['file'], $options['line'])) {
                    $file = $options['file'];
                    $line = $options['line'];
                } elseif (isset($options['trace'])) {
                    $file = $options['trace'][0]['file'];
                    $line = $options['trace'][0]['line'];
                } else {
                    foreach (debug_backtrace(2) as $item) {
                        if ($item['file'] !== __FILE__) {
                            $file = $item['file'];
                            $line = $item['line'];

                            break;
                        }
                    }
                }

                $message .= " in $file:$line";
            }
        }

        $message = sprintf("[SFW %s] %s", ucfirst($level), $message);

        $timezonePrev = date_default_timezone_get();

        if ($timezonePrev === $this->timezone || !date_default_timezone_set($this->timezone)) {
            $timezonePrev = null;
        }

        if (!isset($options['destination'])) {
            error_log($message);

            if (self::$sys['config']['logger_file'] !== null) {
                $options['destination'] = self::$sys['config']['logger_file'];
            }
        }

        if (isset($options['destination'])) {
            $message = sprintf("[%s] %s\n", date('d-M-Y H:i:s e'), $message);

            self::sys('File')->put($options['destination'], $message, FILE_APPEND);
        }

        if ($timezonePrev !== null) {
            date_default_timezone_set($timezonePrev);
        }

        restore_error_handler();
    }

    /**
     * Logs database slow query.
     */
    public function dbSlowQuery(float $timer, array $queries): void
    {
        if (self::$sys['config']['db_slow_queries_log'] === null
            || $timer <= self::$sys['config']['db_slow_queries_min']
        ) {
            return;
        }

        $host = idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'];

        $queries = implode("\n\t", array_map(fn($a) => self::sys('Text')->fTrim($a), $queries));

        $message = sprintf("[%.2f] %s\n\t%s\n", $timer, $host, $queries);

        $this->info($message, options: [
            'destination' => self::$sys['config']['db_slow_queries_log'],
            'append_file_and_line' => false,
        ]);
    }

    /**
     * Logs transactions fails.
     */
    public function transactionFail(string $level, string $state, int $retry): void
    {
        if (self::$sys['config']['transaction_fails_log'] === null) {
            return;
        }

        $host = idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'];

        $message = sprintf("[%s] [%d] %s", $state, $retry, $host);

        $this->log($level, $message, options: [
            'destination' => self::$sys['config']['transaction_fails_log'],
            'append_file_and_line' => false,
        ]);
    }

    /**
     * Sets timezone.
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Gets timezone.
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }
}
