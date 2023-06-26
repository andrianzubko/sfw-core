<?php

namespace SFW\Lazy\Sys;

/**
 * Pgsql.
 */
class Pgsql extends \SFW\Lazy\Sys
{
    /**
     * Profiler.
     */
    protected ?\Closure $profiler = null;

    /**
     * Just in case of changing profiler.
     */
    public function __construct() {}

    /**
     * Pgsql module instance.
     */
    public function getInstance(): object
    {
        $profiler = $this->profiler;

        if (!isset($profiler)
            && isset(self::$config['db_slow_queries_log'])
        ) {
            $profiler = function (float $microtime, array $queries): void {
                if ($microtime >= self::$config['db_slow_queries_min']) {
                    self::$sys->logger()->save(self::$config['db_slow_queries_log'],
                        sprintf("[%.2f] %s\n\t%s\n",
                            $microtime,
                                idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'],
                                    implode("\n\t", array_map(fn($a) => self::$sys->text()->fulltrim($a), $queries))
                        )
                    );
                }
            };
        }

        try {
            $db = new \SFW\Databaser\Pgsql(self::$config['pgsql'], $profiler);
        } catch (\SFW\Databaser\Exception $error) {
            self::$sys->abend()->error($error->getMessage());
        }

        return $db;
    }
}
