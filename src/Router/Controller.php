<?php

declare(strict_types=1);

namespace SFW\Router;

/**
 * Controllers router.
 */
final class Controller extends \SFW\Router
{
    /**
     * Internal cache.
     */
    protected static array|false $cache;

    /**
     * Reads and actualizes cache if needed.
     *
     * @throws \SFW\Exception\Runtime
     */
    public function __construct()
    {
        if (!isset(self::$cache)) {
            $this->readCache(self::$sys['config']['router_controllers_cache']);
        }
    }

    /**
     * Gets current action.
     */
    public function getCurrentAction(): array|false
    {
        $matches = self::$cache['static'][$_SERVER['REQUEST_PATH']] ?? false;

        if ($matches === false
            && preg_match(self::$cache['regex'], $_SERVER['REQUEST_PATH'], $M)
        ) {
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

        if (!\is_array($match)) {
            $match = [$match, null];
        }

        $action = [];

        $action['full'] = $match[0];

        $action['short'] = basename(strtr($action['full'], '\\', '/'));

        $action['class'] = strtok($action['short'], '::');

        $action['alias'] = $match[1];

        return $action;
    }

    /**
     * Generates URL by action and optional parameters.
     */
    public function genUrl(string $action, string|int|float|null ...$params): string
    {
        $pCount = \count($params);

        $index = self::$cache['actions']["$action:$pCount"] ?? null;

        if ($index === null) {
            $lcAction = lcfirst($action);

            $index = self::$cache['actions']["$action::$lcAction:$pCount"] ?? null;
        }

        if ($index === null) {
            $message = "Unable to make URL by action $action";

            self::sys('Logger')->warning(
                $pCount
                    ? sprintf("$message and $pCount %s",
                        $pCount === 1 ? 'parameter' : 'parameters',
                    )
                    : $message,
                options: debug_backtrace(2)[1],
            );

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

    /**
     * Rebuilds cache.
     */
    protected function rebuildCache(array $initialCache): void
    {
        self::$cache = $initialCache;

        self::$cache['static'] = [];

        self::$cache['dynamic'] = [];

        self::$cache['urls'] = [];

        self::$cache['actions'] = [];

        self::$cache['regex'] = [];

        foreach (get_declared_classes() as $class) {
            if (!str_starts_with($class, 'App\\')) {
                continue;
            }

            $rClass = new \ReflectionClass($class);

            foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($rMethod->getAttributes(\SFW\AsController::class) as $rAttribute) {
                    if ($rMethod->isConstructor()) {
                        self::sys('Logger')->warning("Constructor can't be a controller", options: [
                            'file' => $rMethod->getFileName(),
                            'line' => $rMethod->getStartLine(),
                        ]);

                        continue;
                    }

                    $instance = $rAttribute->newInstance();

                    foreach ($instance->url as $url) {
                        foreach ($instance->method as $method) {
                            if ($instance->alias !== null) {
                                self::$cache['static'][$url][$method] = [
                                    "$class::$rMethod->name", $instance->alias,
                                ];
                            } else {
                                self::$cache['static'][$url][$method] = "$class::$rMethod->name";
                            }
                        }
                    }
                }
            }
        }

        foreach (self::$cache['static'] as $url => $actions) {
            if (preg_match_all('/{([^}]+)}/', $url, $M)) {
                unset(self::$cache['static'][$url]);

                self::$cache['regex'][] = sprintf("%s(*:%d)",
                    preg_replace('/\\\\{[^}]+}/', '([^/]+)', preg_quote($url)),
                        \count(self::$cache['dynamic']),
                );

                self::$cache['dynamic'][] = [$actions, $M[1]];

                self::$cache['urls'][] = preg_split('/({[^}]+})/', $url,
                    flags: PREG_SPLIT_DELIM_CAPTURE,
                );

                $pCount = \count($M[1]);
            } else {
                self::$cache['urls'][] = $url;

                $pCount = 0;
            }

            foreach ($actions as $action) {
                if (\is_string($action)) {
                    $action = [$action, null];
                }

                foreach ([$action[0], basename(strtr($action[0], '\\', '/')), $action[1]] as $name) {
                    if ($name !== null) {
                        self::$cache['actions']["$name:$pCount"] = \count(self::$cache['urls']) - 1;
                    }
                }
            }
        }

        self::$cache['regex'] = sprintf('{^(?|%s)$}', implode('|', self::$cache['regex']));
    }
}
