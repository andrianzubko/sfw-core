<?php

namespace SFW\Lazy;

/**
 * Database.
 */
class Db extends \SFW\Lazy
{
    /**
     * Database module instance.
     */
    public function getInstance(): object
    {
        try {
            $db = \SFW\Databaser::init(self::$config['dbDriver'], self::$config['dbOptions'], $this->getProfiler());
        } catch (\SFW\Databaser\Exception $error) {
            $this->abend()->error($error->getMessage());
        }

        return $db;
    }

    /**
     * Getting profiler for long queries.
     */
    protected function getProfiler(): ?\Closure
    {
        if (!isset(self::$config['dbSlowQueriesLog'])) {
            return null;
        }

        return function (float $microtime, array $queries): void {
            if ($microtime < self::$config['dbSlowQueriesMin']) {
                return;
            }

            $this->logger()->save(self::$config['dbSlowQueriesLog'],
                sprintf("[%.2f] %s\n\t%s\n",
                    $microtime,
                        idn_to_utf8($_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'],
                            implode("\n\t", array_map(fn($a) => $this->text()->fulltrim($a), $queries))
                )
            );
        };
    }
}
