<?php
declare(strict_types=1);

namespace SFW\Router;

/**
 * Routes from request url to Controller action.
 */
final class Controller extends \SFW\Router
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
            self::$cache = (new \SFW\Registry\Controllers())->getCache();
        }
    }

    /**
     * Gets action.
     */
    public function getAction(): array|false
    {
        $matches = self::$cache['static'][$_SERVER['REQUEST_PATH']] ?? false;

        if ($matches === false && preg_match(self::$cache['regex'], $_SERVER['REQUEST_PATH'], $M)) {
            [$matches, $keys] = self::$cache['dynamic'][$M['MARK']];

            foreach ($keys as $i => $key) {
                $_GET[$key] = $_REQUEST[$key] = $M[$i + 1];
            }
        }

        if ($matches === false) {
            return false;
        }

        $match = $matches[$_SERVER['REQUEST_METHOD']] ?? $matches[''] ?? false;

        if ($match === false) {
            return false;
        }

        if (\is_string($match)) {
            $match = [$match, null];
        }

        $action = [];

        $action['full'] = \is_array($match) ? $match[0] : $match;

        $action['short'] = basename(strtr($action['full'], '\\', '/'));

        $action['alias'] = \is_array($match) ? $match[1] : null;

        return $action;
    }

    /**
     * Generates URL by action and optional parameters.
     */
    public function genUrl(string $action, string|int|float|null ...$params): string
    {
        $pCount = \count($params);

        $index = self::$cache['actions']["$action $pCount"]
            ?? self::$cache['actions']["$action::" . lcfirst($action) . " $pCount"]
            ?? null;

        if ($index === null) {
            $message = "Unable to make URL by action $action";

            if ($pCount) {
                $message .= sprintf(" and $pCount %s",
                    $pCount === 1 ? 'parameter' : 'parameters'
                );
            }

            self::sys('Logger')->warning($message, options: debug_backtrace(2)[1]);

            return '/';
        }

        $url = self::$cache['urls'][$index];

        if ($params) {
            foreach ($params as $i => $value) {
                if ($value !== null) {
                    $url[$i * 2 + 1] = $value;
                }
            }

            return implode($url);
        }

        return $url;
    }
}
