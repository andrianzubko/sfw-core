<?php

namespace SFW\Router;

/**
 * Routes from request url to Controller action.
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
    protected static array $cFiles;

    /**
     * Gets action, full class and method name.
     *
     * @throws \SFW\RuntimeException
     */
    public static function getTarget(): array
    {
        $controller = new static();

        if (self::$cache === false) {
            self::$cache = @include self::$config['sys']['router']['cache'];

            if (self::$cache === false
                || self::$config['sys']['env'] !== 'prod'
                    && $controller->isOutdated()
            ) {
                $controller->rebuild();
            }
        }

        return $controller->findInCache();
    }

    /**
     * Generates URL by action (or FQMN) and optional parameters.
     *
     * @throws \SFW\RuntimeException
     */
    public function genUrl(string $action, string|int|float|null ...$params): string
    {
        if (self::$cache === false) {
            self::$cache = @include self::$config['sys']['router']['cache'];

            if (self::$cache === false) {
                $this->rebuild();
            }
        }

        $pCount = count($params);

        $url = self::$cache['urls'][$action][$pCount]
            ?? self::$cache['urls'][$this->FullToAction($action)][$pCount]
            ?? null;

        if (!isset($url)) {
            if ($pCount) {
                $this->sys('Logger')->warning(
                    sprintf(
                        'Unable to make URL with %d parameter%s by action %s',
                            $pCount,
                            $pCount === 1 ? '' : 's',
                            $action
                    ), debug_backtrace(2)[1]
                );
            } else {
                $this->sys('Logger')->warning(
                    sprintf(
                        'Unable to make URL by action %s',
                            $action
                    ), debug_backtrace(2)[1]
                );
            }

            return '/';
        }

        if ($params) {
            foreach ($params as $i => $value) {
                if (isset($value)) {
                    $url[$i * 2 + 1] = $value;
                }
            }

            return implode($url);
        }

        return $url;
    }

    /**
     * Finds action in cache and transforms to usable variant.
     *
     * Note: no checks for cache existence!
     */
    protected function findInCache(): array
    {
        $actions = self::$cache['static'][$_SERVER['REQUEST_PATH']] ?? null;

        if (!isset($actions)
            && preg_match(self::$cache['regex'], $_SERVER['REQUEST_PATH'], $M)
        ) {
            [$actions, $keys] = self::$cache['dynamic'][$M['MARK']];

            foreach ($keys as $i => $key) {
                $_GET[$key] = $_REQUEST[$key] = $M[$i + 1];
            }
        }

        if (isset($actions)) {
            $action = $actions[$_SERVER['REQUEST_METHOD']] ?? $actions[''] ?? null;

            if (isset($action)) {
                $chunks = explode('::', "App\\Controller\\$action");

                return [
                    $chunks[0],
                    $chunks[1] ?? '__construct',
                    $action,
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
        if (!isset(self::$cFiles)) {
            self::$cFiles = [];

            foreach ($this->sys('Dir')->scan(APP_DIR . '/src/Controller', true, true) as $item) {
                if (is_file($item)
                    && str_ends_with($item, '.php')
                ) {
                    self::$cFiles[] = $item;
                }
            }
        }

        return self::$cFiles;
    }

    /**
     * Rechecks of the needs for rescanning.
     *
     * Note: no checks for cache existence!
     */
    protected function isOutdated(): bool
    {
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
            'static' => [],
            'dynamic' => [],
            'urls' => [],
            'regex' => [],
        ];

        $objects = [];

        foreach (get_declared_classes() as $class) {
            if (str_starts_with($class, 'App\\Controller\\')) {
                $rClass = new \ReflectionClass($class);

                $objects[] = [$rClass, $class];

                foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                    if ($rMethod->class === $class) {
                        $objects[] = [$rMethod, "$class::$rMethod->name"];
                    }
                }
            }
        }

        foreach ($objects as [$object, $name]) {
            foreach ($object->getAttributes('SFW\\Route') as $attribute) {
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
                        count(self::$cache['dynamic'])
                );

                self::$cache['dynamic'][] = [$actions, $M[1]];

                foreach ($actions as $action) {
                    self::$cache['urls'][$action][count($M[1])] = preg_split('/({[^}]+})/', $url,
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
     * Makes action from fully qualified method name.
     */
    protected function FullToAction(string $name): string
    {
        return preg_replace('/(?:^App\\\\Controller\\\\|::__construct$)/', '', $name);
    }
}
