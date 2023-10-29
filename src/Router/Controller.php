<?php

namespace SFW\Router;

use SFW\Exception\Runtime;

/**
 * Routes from request url to Controller action.
 */
final class Controller extends \SFW\Router
{
    /**
     * Internal cache.
     */
    protected static array|false $cache;

    /**
     * Controller files.
     */
    protected static array $cFiles;

    /**
     * Checks and actualize cache if needed.
     */
    public function __construct()
    {
        if (!isset(self::$cache)) {
            self::$cache = @include self::$sys['config']['router_cache'];

            if (self::$cache === false || self::$sys['config']['env'] !== 'prod' && $this->isOutdated()) {
                $this->rebuild();
            }
        }
    }

    /**
     * Gets class, method and action names.
     *
     * @throws Runtime
     */
    public function getTarget(): object|false
    {
        $actions = self::$cache['static'][$_SERVER['REQUEST_PATH']] ?? null;

        if ($actions === null && preg_match(self::$cache['regex'], $_SERVER['REQUEST_PATH'], $M)) {
            [$actions, $keys] = self::$cache['dynamic'][$M['MARK']];

            foreach ($keys as $i => $key) {
                $_GET[$key] = $_REQUEST[$key] = $M[$i + 1];
            }
        }

        if ($actions !== null) {
            $target = (object) [];

            $target->action = $actions[$_SERVER['REQUEST_METHOD']] ?? $actions[''] ?? null;

            if ($target->action !== null) {
                $chunks = explode('::', "App\\Controller\\$target->action");

                $target->class = $chunks[0];

                $target->method = $chunks[1] ?? '__construct';

                return $target;
            }
        }

        return false;
    }

    /**
     * Generates URL by action (or FQMN) and optional parameters.
     *
     * @throws Runtime
     */
    public function genUrl(string $action, string|int|float|null ...$params): string
    {
        $pCount = \count($params);

        $url = self::$cache['urls'][$action][$pCount]
            ?? self::$cache['urls'][$this->FullToAction($action)][$pCount]
            ?? null;

        if ($url === null) {
            if ($pCount) {
                self::sys('Logger')->warning(
                    sprintf(
                        'Unable to make URL with %d parameter%s by action %s',
                            $pCount,
                            $pCount === 1 ? '' : 's',
                            $action
                    ), debug_backtrace(2)[1]
                );
            } else {
                self::sys('Logger')->warning(
                    sprintf('Unable to make URL by action %s', $action), debug_backtrace(2)[1]
                );
            }

            return '/';
        }

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
     * Gets controller files.
     */
    protected function scanForControllerFiles(): void
    {
        if (!isset(self::$cFiles)) {
            self::$cFiles = [];

            foreach (self::sys('Dir')->scan(APP_DIR . '/src/Controller', true, true) as $item) {
                if (is_file($item) && str_ends_with($item, '.php')) {
                    self::$cFiles[] = $item;
                }
            }
        }
    }

    /**
     * Rechecks of the needs for rescanning.
     */
    protected function isOutdated(): bool
    {
        $this->scanForControllerFiles();

        if (self::$cache['count'] !== \count(self::$cFiles)) {
            return true;
        }

        foreach (self::$cFiles as $file) {
            if ((int) filemtime($file) > self::$cache['time']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rescans controllers and rebuilds cache.
     *
     * @throws Runtime
     */
    protected function rebuild(): void
    {
        $this->scanForControllerFiles();

        foreach (self::$cFiles as $file) {
            require_once $file;
        }

        self::$cache = [];

        self::$cache['time'] = time();

        self::$cache['count'] = \count(self::$cFiles);

        self::$cache['static'] = [];

        self::$cache['dynamic'] = [];

        self::$cache['urls'] = [];

        self::$cache['regex'] = [];

        $objects = [];

        foreach (get_declared_classes() as $class) {
            if (str_starts_with($class, 'App\Controller\\')) {
                $rClass = new \ReflectionClass($class);

                $objects[] = [$rClass, $class];

                foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                    $objects[] = [$rMethod, "$class::$rMethod->name"];
                }
            }
        }

        foreach ($objects as [$object, $name]) {
            foreach ($object->getAttributes(\SFW\Route::class) as $attribute) {
                $route = $attribute->newInstance();

                foreach ($route->url as $url) {
                    foreach ($route->method as $method) {
                        self::$cache['static'][$url][$method] = $this->FullToAction($name);
                    }
                }
            }
        }

        foreach (self::$cache['static'] as $url => $actions) {
            if (preg_match_all('/{([^}]+)}/', $url, $M)) {
                unset(self::$cache['static'][$url]);

                self::$cache['regex'][] = sprintf("%s(*:%d)",
                    preg_replace('/\\\\{[^}]+}/', '([^/]+)', preg_quote($url)),
                        \count(self::$cache['dynamic'])
                );

                self::$cache['dynamic'][] = [$actions, $M[1]];

                foreach ($actions as $action) {
                    self::$cache['urls'][$action][\count($M[1])] = preg_split('/({[^}]+})/', $url,
                        flags: PREG_SPLIT_DELIM_CAPTURE
                    );
                }
            } else {
                foreach ($actions as $action) {
                    self::$cache['urls'][$action][0] = $url;
                }
            }
        }

        self::$cache['regex'] = sprintf('{^(?|%s)$}', implode('|', self::$cache['regex']));

        if (!self::sys('File')->putVar(self::$sys['config']['router_cache'], self::$cache, LOCK_EX)) {
            throw new Runtime(
                sprintf('Unable to write file %s', self::$sys['config']['router_cache'])
            );
        }
    }

    /**
     * Makes action from fully qualified method name.
     */
    protected function FullToAction(string $name): string
    {
        return preg_replace('/(?:^App\\\\Controller\\\\|::__construct$)/', '', $name);
    }
}
