<?php

namespace SFW\Lazy\Sys;

/**
 * Logger.
 */
class Logger extends \SFW\Lazy\Sys
{
    /**
     * Logging database slow query.
     */
    public function dbSlowQuery(float $timer, array $queries): void
    {
        if (!isset(self::$config['sys']['db']['slow_queries_log'])
            || $timer < self::$config['sys']['db']['slow_queries_min']
        ) {
            return;
        }

        $host = idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'];

        $queries = implode("\n\t",
            array_map(
                fn($a) => $this->sys('Text')->fulltrim($a), $queries
            )
        );

        $this->save(self::$config['sys']['db']['slow_queries_log'],
            sprintf("[%.2f] %s\n\t%s\n",
                $timer, $host, $queries
            )
        );
    }

    /**
     * Logging transactions fails.
     */
    public function transactionFail(string $state, int $retry): void
    {
        if (!isset(self::$config['sys']['transaction']['fails_log'])) {
            return;
        }

        $host = idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'];

        $this->save(self::$config['sys']['transaction']['fails_log'],
            sprintf("[%s] [%d] %s",
                $state, $retry, $host
            )
        );
    }

    /**
     * Logging in time with default timezone.
     */
    public function save(string $file, string $message): void
    {
        $timezonePrev = date_default_timezone_get();

        if ($timezonePrev === self::$config['sys']['timezone']) {
            $timezonePrev = null;
        } else {
            date_default_timezone_set(self::$config['sys']['timezone']);
        }

        $message = sprintf("[%s] %s\n", date('d.m.y H:i'), $message);

        $this->sys('File')->put($file, $message, FILE_APPEND);

        if (isset($timezonePrev)) {
            date_default_timezone_set($timezonePrev);
        }
    }
}
