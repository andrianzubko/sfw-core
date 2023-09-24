<?php

namespace SFW\Router;

/**
 * Routes from request url to Controller class.
 */
class Controller extends \SFW\Router
{
    /**
     * Internal cache.
     */
    protected static array|false $cache = false;

    /**
     * Controller files.
     */
    protected array $cFiles;

    /**
     * Makes URL by action (or full namespace) and optional parameters.
     *
     * @throws \SFW\RuntimeException
     */
    public static function makeUrl(string $action, string|int|float ...$params): string
    {
        if (self::$cache === false) {
            self::$cache = @include self::$config['sys']['router']['cache'];

            if (self::$cache === false
                || self::$config['sys']['env'] !== 'prod'
            ) {
                $controller = new self();

                if ($controller->isOutdated()) {
                    $controller->rebuild();
                }
            }
        }

        $url = self::$cache['urls'][$action][count($params)] ?? null;

        if (!isset($url)) {
            $action = str_replace(['App\\Controller\\', '::__construct'], '', $action);

            $url = self::$cache['urls'][$action][count($params)] ?? '/';
        }

        foreach ($params as $param) {
            $url = preg_replace('/{[^}]+}/', $param, $url, 1);
        }

        return $url;
    }

    /**
     * Gets full class name, method and action.
     *
     * @throws \SFW\RuntimeException
     */
    protected function get(): array
    {
        if (self::$cache === false) {
            self::$cache = @include self::$config['sys']['router']['cache'];
        }

        if (self::$cache !== false
            && self::$config['sys']['env'] === 'prod'
        ) {
            return $this->findInCache();
        }

        if ($this->isOutdated()) {
            $this->rebuild();
        }

        return $this->findInCache();
    }

    /**
     * Finds action in cache and transforms to usable variant.
     */
    protected function findInCache(): array
    {
        if (self::$cache !== false
            && preg_match(self::$cache['regex'], $_SERVER['REQUEST_URL'], $M)
        ) {
            $actions = self::$cache['actions'][$M['MARK']];

            $action = $actions[$_SERVER['REQUEST_METHOD']] ?? $actions[''] ?? null;

            if (isset($action)) {
                $keys = self::$cache['keys'][$M['MARK']] ?? null;

                if (isset($keys)) {
                    foreach ($keys as $i => $key) {
                        $_GET[$key] = $_REQUEST[$key] = $M[$i + 1];
                    }
                }

                return
                    array_pad(
                        explode(
                            '::', "App\\Controller\\$action", 2
                        ), 2, '__construct'
                    ) + [
                        2 => $action
                    ];
            }
        }

        return [false, false, false];
    }

    /**
     * Gets controller files.
     */
    protected function getControllerFiles(): array
    {
        if (!isset($this->cFiles)) {
            $this->cFiles = [];

            foreach (
                $this->sys('Dir')->scan(APP_DIR . '/src/Controller', true, true) as $item
            ) {
                if (str_ends_with($item, '.php')
                    && is_file($item)
                ) {
                    $this->cFiles[] = $item;
                }
            }
        }

        return $this->cFiles;
    }

    /**
     * Rechecks of the needs for rescanning.
     */
    protected function isOutdated(): bool
    {
        if (self::$cache === false) {
            return true;
        }

        foreach ($this->getControllerFiles() as $file) {
            if ((int) filemtime($file) > self::$cache['time']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rescans controllers and rebuilds cache.
     *
     * @throws \SFW\RuntimeException
     */
    protected function rebuild(): void
    {
        foreach ($this->getControllerFiles() as $file) {
            require_once $file;
        }

        self::$cache = [
            'time' => time(),
            'actions' => [],
            'keys' => [],
            'urls' => [],
            'regex' => [],
        ];

        foreach (get_declared_classes() as $class) {
            if (str_starts_with($class, 'App\\Controller\\')) {
                $rClass = new \ReflectionClass($class);

                $this->saveRouteToCache($rClass, substr($class, 15));

                foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                    if ($rMethod->class === $class) {
                        $this->saveRouteToCache(
                            $rMethod,
                            $rMethod->isConstructor()
                                ? substr($class, 15)
                                : substr($class, 15) . '::' . $rMethod->name
                        );
                    }
                }
            }
        }

        $i = 0;

        foreach (self::$cache['actions'] as $url => $actions) {
            if (preg_match_all('/{([^}]+)}/', $url, $M)) {
                self::$cache['keys'][$i] = $M[1];
            }

            foreach ($actions as $action) {
                self::$cache['urls'][$action][count(self::$cache['keys'][$i] ?? [])] ??= $url;
            }

            self::$cache['regex'][] = sprintf("%s(*:$i)",
                preg_replace('/\\\\{[^}]+}/', '([^/]+)', preg_quote($url))
            );

            $i++;
        }

        self::$cache['regex'] = sprintf('{^(?|%s)$}', implode('|', self::$cache['regex']));

        self::$cache['actions'] = array_values(self::$cache['actions']);

        if (!$this->sys('File')->putVar(
                self::$config['sys']['router']['cache'], self::$cache, LOCK_EX)
        ) {
            throw new \SFW\RuntimeException(
                sprintf(
                    'Unable to write file %s',
                        self::$config['sys']['router']['cache']
                )
            );
        }
    }

    /**
     * Reflection item to cache structure.
     */
    private function saveRouteToCache(\ReflectionClass | \ReflectionMethod $item, string $action): void
    {
        foreach ($item->getAttributes('SFW\\Route') as $attribute) {
            $route = $attribute->newInstance();

            foreach ($route->url as $url) {
                foreach ($route->method as $method) {
                    self::$cache['actions'][$url][$method] = $action;
                }
            }
        }
    }
}
