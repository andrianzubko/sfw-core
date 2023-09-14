<?php

namespace SFW\Lazy\Sys;

use Psr\Log\{LoggerInterface, LoggerTrait, LogLevel};

/**
 * Logger.
 */
class Logger extends \SFW\Lazy\Sys implements LoggerInterface
{
    use LoggerTrait;

    /**
     * Logs with an arbitrary level.
     */
    public function log(mixed $level, string|\Stringable $message, array $context = []): void
    {
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
                $psrLogDir = dirname(
                    (new \ReflectionClass(LoggerTrait::class))->getFileName()
                );

                foreach (debug_backtrace() as $item) {
                    if (!str_starts_with($item['file'], $psrLogDir)) {
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

        if ($tzPrev === $context['timezone']) {
            $tzPrev = null;
        } else {
            date_default_timezone_set($context['timezone']);
        }

        if (isset($context['destination'])) {
            $this->sys('Dir')->create(
                dirname($context['destination'])
            );

            error_log(
                sprintf("[%s] %s\n",
                    date('d-M-Y H:i:s e'), $message
                ), 3, $context['destination']
            );
        } else {
            error_log($message);
        }

        if (isset($tzPrev)) {
            date_default_timezone_set($tzPrev);
        }
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
                    fn($a) => $this->sys('Text')->fulltrim($a), $queries
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
