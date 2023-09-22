<?php

namespace SFW\Router;

/**
 * Route from request url to Controller class.
 */
class Controller extends \SFW\Router
{
    /**
     * Controllers files.
     */
    protected array $cFiles;

    /**
     * Gets full class name, method and action.
     *
     * @throws \SFW\RuntimeException
     */
    public function get(): array
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
     * Just check and rebuild cache if outdated.
     *
     * @throws \SFW\RuntimeException
     */
    public function recheck(): void
    {
        if (self::$cache === false) {
            self::$cache = @include self::$config['sys']['router']['cache'];
        }

        if ($this->isOutdated()) {
            $this->rebuild();
        }
    }

    /**
     * Finds action in cache and transform to usable variant.
     */
    protected function findInCache(): array
    {
        if (self::$cache !== false
            && preg_match(self::$cache['regex'], $_SERVER['REQUEST_URL'], $M)
        ) {
            $found = self::$cache['in'][$M['MARK']];

            if (!isset($found['method'])
                || in_array($_SERVER['REQUEST_METHOD'], $found['method'], true)
            ) {
                if (isset($found['keys'])) {
                    foreach ($found['keys'] as $i => $key) {
                        $_GET[$key] = $_REQUEST[$key] = $M[$i + 1];
                    }
                }

                return
                    array_pad(
                        explode(
                            '::', 'App\\Controller\\' . $found['action'], 2
                        ), 2, '__construct'
                    ) + [
                        2 => $found['action']
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
        $cFiles = [];

        foreach ($this->sys('Dir')->scan(APP_DIR . '/src/Controller', true, true) as $item) {
            if (is_file($item)
                && str_ends_with($item, '.php')
            ) {
                $cFiles[] = $item;
            }
        }

        return $cFiles;
    }

    /**
     * Recheck of the needs for rescanning.
     */
    protected function isOutdated(): bool
    {
        if (self::$cache === false) {
            return true;
        }

        if (!isset($this->cFiles)) {
            $this->cFiles = $this->getControllerFiles();
        }

        foreach ($this->cFiles as $file) {
            if ((int) filemtime($file) > self::$cache['time']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rescan controllers and rebuild cache.
     *
     * @throws \SFW\RuntimeException
     */
    protected function rebuild(): void
    {
        if (!isset($this->cFiles)) {
            $this->cFiles = $this->getControllerFiles();
        }

        foreach ($this->cFiles as $file) {
            require_once $file;
        }

        self::$cache = [
            'time' => time(),
            'in' => [],
            'out' => [],
            'regex' => [],
        ];

        foreach (get_declared_classes() as $class) {
            if (str_starts_with($class, 'App\\Controller\\')) {
                $rClass = new \ReflectionClass($class);

                foreach ($rClass->getAttributes('SFW\\Route') as $attribute) {
                    $route = $attribute->newInstance();

                    self::$cache['in'][$route->url] = array_filter([
                        'action' => substr($class, 15),
                        'method' => $route->method,
                    ]);
                }

                foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                    if ($rMethod->class === $class) {
                        foreach ($rMethod->getAttributes('SFW\\Route') as $attribute) {
                            $route = $attribute->newInstance();

                            self::$cache['in'][$route->url] = array_filter([
                                'action' => substr($class, 15) . (
                                    $rMethod->isConstructor()
                                        ? '' : '::' . $rMethod->name
                                ),
                                'method' => $route->method,
                            ]);
                        }
                    }
                }
            }
        }

        foreach (self::$cache['in'] as $url => $item) {
            if (preg_match_all('/{([^}]+)}/', $url, $M)) {
                self::$cache['in'][$url]['keys'] = $M[1];
            }

            self::$cache['out'][$item['action']] = $url;

            self::$cache['regex'][] = sprintf('%s(*:%s)',
                preg_replace('/\\\\{[^}]+}/', '([^/]+)', preg_quote($url)),
                    count(self::$cache['regex'])
            );
        }

        self::$cache['regex'] = sprintf('{^(?|%s)$}',
            implode('|', self::$cache['regex'])
        );

        self::$cache['in'] = array_values(self::$cache['in']);

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
}
