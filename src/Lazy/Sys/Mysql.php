<?php

namespace SFW\Lazy\Sys;

/**
 * Mysql.
 *
 * @mixin \SFW\Databaser\Driver
 */
class Mysql extends \SFW\Lazy\Sys
{
    /**
     * Profiler.
     */
    protected ?\Closure $profiler = null;

    /**
     * Mysql module instance.
     *
     * @internal
     */
    public function getInstance(): \SFW\Databaser\Driver
    {
        $profiler = $this->profiler;

        if (!isset($profiler)
            && isset(self::$config['sys']['db_slow_queries_log'])
        ) {
            $profiler = function (float $microtime, array $queries): void {
                if ($microtime >= self::$config['sys']['db_slow_queries_min']) {
                    $this->sys('Logger')->save(self::$config['sys']['db_slow_queries_log'],
                        sprintf("[%.2f] %s\n\t%s\n",
                            $microtime,
                                idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'],
                                    implode("\n\t", array_map(fn($a) => $this->sys('Text')->fulltrim($a), $queries))
                        )
                    );
                }
            };
        }

        try {
            $db = new \SFW\Databaser\Mysql(self::$config['sys']['mysql'], $profiler);
        } catch (\SFW\Databaser\Exception $error) {
            $this->sys('Abend')->error($error->getMessage());
        }

        return $db;
    }
}
