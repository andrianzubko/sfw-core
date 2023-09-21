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
    public function get(): string|false
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
    protected function findClass(array $cache): string|false
    {
        if (preg_match($cache['regex'], $_SERVER['REQUEST_URL'], $M)) {
            $route = $cache['routes'][$M['MARK']];

            if (!$route['methods']
                || in_array($_SERVER['REQUEST_METHOD'], $route['methods'], true)
            ) {
                if (isset($route['keys'])) {
                    foreach ($route['keys'] as $i => $key) {
                        $_GET[$key] = $_REQUEST[$key] = $M[$i + 1];
                    }
                }

                return $route['class'];
            }
        }

        return false;
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
            'routes' => [],
            'paths' => [],
        ];

        foreach (get_declared_classes() as $class) {
            if (str_starts_with($class, 'App\\Controller\\')) {
                $attributes = (new \ReflectionClass($class))->getAttributes();

                foreach ($attributes as $attribute) {
                    $cache['routes'][] = [
                        'class' => $class,
                        ...(array) $attribute->newInstance()
                    ];
                }
            }
        }

        $cache['regex'] = sprintf('{^(?|%s)$}',
            implode('|',
                array_map(
                    fn($i, $route) => sprintf("%s(*:$i)",
                        preg_replace('/\\\\{[^}]+}/', '([^/]+)',
                            preg_quote($route['path'])
                        )
                    ), array_keys($cache['routes']), $cache['routes']
                )
            )
        );

        foreach ($cache['routes'] as $i => $route) {
            if (preg_match_all('/{([^}]+)}/', $route['path'], $M)) {
                $route['keys'] = $M[1];
            }

            $cache['paths'][$route['class']] = $route['path'];

            unset($route['path']);

            $cache['routes'][$i] = $route;
        }

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
