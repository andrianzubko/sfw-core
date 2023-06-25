<?php

namespace SFW\Lazy;

/**
 * Mysql.
 */
class Mysql extends \SFW\Lazy
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
     * Mysql module instance.
     */
    public function getInstance(): object
    {
        $profiler = $this->profiler;

        if (!isset($profiler)
            && isset(self::$config['dbSlowQueriesLog'])
        ) {
            $profiler = function (float $microtime, array $queries): void {
                if ($microtime >= self::$config['dbSlowQueriesMin']) {
                    $this->logger()->save(self::$config['dbSlowQueriesLog'],
                        sprintf("[%.2f] %s\n\t%s\n",
                            $microtime,
                                idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'],
                                    implode("\n\t", array_map(fn($a) => $this->text()->fulltrim($a), $queries))
                        )
                    );
                }
            };
        }

        try {
            $db = new \SFW\Databaser\Mysql(self::$config['mysql'], $profiler);
        } catch (\SFW\Databaser\Exception $error) {
            $this->abend()->error($error->getMessage());
        }

        return $db;
    }
}
