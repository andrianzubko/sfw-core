<?php

namespace SFW\Router;

/**
 * Route from request url to Controller class.
 */
class Controller extends \SFW\Router
{
    /**
     * Gets target class name.
     *
     * @throws \SFW\RuntimeException
     */
    public function get(): array
    {
        $cache = @include self::$config['sys']['router']['cache'];

        if ($cache !== false
            && self::$config['sys']['env'] === 'prod'
        ) {
            return $this->findClass($cache);
        }

        $cFiles = $this->getControllerFiles();

        $cache = $this->checkVersion($cache, $cFiles);

        if ($cache === false) {
            $cache = $this->rebuild($cFiles);
        }

        return $this->findClass($cache);
    }

    /**
     * Finds target Controller class.
     */
    protected function findClass(array $cache): array
    {
        if (preg_match($cache['regex'], $_SERVER['REQUEST_URL'], $M)) {
            $found = $cache['in'][$M['MARK']];

            if (empty($found['method'])
                || in_array($_SERVER['REQUEST_METHOD'], $found['method'], true)
            ) {
                if (isset($found['keys'])) {
                    foreach ($found['keys'] as $i => $key) {
                        $_GET[$key] = $_REQUEST[$key] = $M[$i + 1];
                    }
                }

                return $found['target'];
            }
        }

        return [false, false, false];
    }

    /**
     * Gets controller files.
     */
    protected function getControllerFiles(): array
    {
        $cDir = APP_DIR . '/src/Controller';

        $cFiles = [];

        foreach ($this->sys('Dir')->scan($cDir, true) as $item) {
            if (is_file("$cDir/$item")
                && str_ends_with($item, '.php')
            ) {
                $cFiles[] = "$cDir/$item";
            }
        }

        return $cFiles;
    }

    /**
     * Recheck of the needs for rescanning.
     */
    protected function checkVersion(array|false $cache, array $cFiles): array|false
    {
        if ($cache === false) {
            return false;
        }

        foreach ($cFiles as $cFile) {
            if ((int) filemtime($cFile) > $cache['time']) {
                return false;
            }
        }

        return $cache;
    }

    /**
     * Rescan controllers and rebuild cache.
     *
     * @throws \SFW\RuntimeException
     */
    protected function rebuild(array $cFiles): array
    {
        foreach ($cFiles as $cFile) {
            require_once $cFile;
        }

        $cache = [
            'time' => time(),
            'in' => [],
            'out' => [],
        ];

        foreach (get_declared_classes() as $class) {
            if (str_starts_with($class, 'App\\Controller\\')) {
                $rClass = new \ReflectionClass($class);

                foreach ($rClass->getAttributes('SFW\\Route') as $attribute) {
                    $route = $attribute->newInstance();

                    $cache['in'][$route->path] = array_filter([
                        'target' => [
                            $class, '__construct'
                        ],
                        'method' => $route->method,
                    ]);
                }

                foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                    if ($rMethod->class === $class) {
                        foreach ($rMethod->getAttributes('SFW\\Route') as $attribute) {
                            $route = $attribute->newInstance();

                            $cache['in'][$route->path] = array_filter([
                                'target' => [
                                    $class, $rMethod->name
                                ],
                                'method' => $route->method,
                            ]);
                        }
                    }
                }
            }
        }

        foreach ($cache['in'] as $path => $item) {
            if ($item['target'][1] === '__construct') {
                $item['target'][] = substr($item['target'][0], 15);
            } else {
                $item['target'][] = implode('::', [
                    substr($item['target'][0], 15), $item['target'][1],
                ]);
            }

            if (preg_match_all('/{([^}]+)}/', $path, $M)) {
                $item['keys'] = $M[1];
            }

            $cache['in'][$path] = $item;

            $cache['out'][$item['target'][2]] = $path;
        }

        $cache['regex'] = sprintf('{^(?|%s)$}',
            implode('|',
                array_map(
                    fn($i, $path) => sprintf("%s(*:$i)",
                        preg_replace('/\\\\{[^}]+}/', '([^/]+)', preg_quote($path))
                    ),
                    array_keys(
                        array_keys($cache['in'])
                    ),
                    array_keys($cache['in'])
                )
            )
        );

        $cache['in'] = array_values($cache['in']);

        if ($this->sys('File')->putVar(self::$config['sys']['router']['cache'], $cache) === false) {
            throw new \SFW\RuntimeException(
                sprintf(
                    'Unable to write file %s',
                        self::$config['sys']['router']['cache']
                )
            );
        }

        return $cache;
    }
}
