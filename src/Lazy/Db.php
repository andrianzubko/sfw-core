<?php

namespace SFW\Lazy;

/**
 * Database.
 */
class Db extends \SFW\Lazy
{
    /**
     * Profiler.
     */
    protected ?Closure $profiler = null;

    /**
     * Just in case of changing profiler.
     */
    public function __construct() {}

    /**
     * Database module initializing.
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
            $db = \SFW\Databaser::init(self::$config['dbDriver'], self::$config['dbOptions'], $profiler);
        } catch (\SFW\Databaser\Exception $error) {
            $this->abend()->error($error->getMessage());
        }

        return $db;
    }
}
