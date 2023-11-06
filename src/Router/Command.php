<?php
declare(strict_types=1);

namespace SFW\Router;

/**
 * Routes from command line arguments to Command action.
 */
final class Command extends \SFW\Router
{
    /**
     * Cache.
     */
    protected static array $cache;

    /**
     * Gets cache.
     */
    public function __construct()
    {
        if (!isset(self::$cache)) {
            self::$cache = (new \SFW\Registry\Commands())->getCache();
        }
    }

    /**
     * Gets action.
     *
     * Very poor implementation. Will be better soon.
     */
    public function getAction(): array|null|false
    {
        if (!isset($_SERVER['argv'][1])) {
            return null;
        }

        $action = [];

        $action['full'] = self::$cache['commands'][$_SERVER['argv'][1]] ?? false;

        if ($action['full'] === false) {
            return false;
        }

        $action['short'] = basename(strtr($action['full'], '\\', '/'));

        $action['alias'] = null;

        return $action;
    }
}
