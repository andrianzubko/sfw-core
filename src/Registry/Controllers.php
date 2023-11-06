<?php

declare(strict_types=1);

namespace SFW\Registry;

/**
 * Registry of listeners.
 */
final class Controllers extends \SFW\Registry
{
    /**
     * Checks and actualize cache if needed.
     *
     * @throws \SFW\Exception\Runtime
     */
    public function __construct()
    {
        parent::__construct(self::$sys['config']['controllers_cache']);
    }

    /**
     * Rebuilds cache.
     *
     * @throws \SFW\Exception\Runtime
     */
    protected function rebuildCache(): void
    {
        $this->cache = [];

        $this->cache['static'] = [];

        $this->cache['dynamic'] = [];

        $this->cache['urls'] = [];

        $this->cache['actions'] = [];

        $this->cache['regex'] = [];

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
                                $this->cache['static'][$url][$method] = [
                                    "$class::$rMethod->name", $instance->alias,
                                ];
                            } else {
                                $this->cache['static'][$url][$method] = "$class::$rMethod->name";
                            }
                        }
                    }
                }
            }
        }

        foreach ($this->cache['static'] as $url => $actions) {
            if (preg_match_all('/{([^}]+)}/', $url, $M)) {
                unset($this->cache['static'][$url]);

                $this->cache['regex'][] = sprintf("%s(*:%d)",
                    preg_replace('/\\\\{[^}]+}/', '([^/]+)', preg_quote($url)),
                        \count($this->cache['dynamic']),
                );

                $this->cache['dynamic'][] = [$actions, $M[1]];

                $this->cache['urls'][] = preg_split('/({[^}]+})/', $url,
                    flags: PREG_SPLIT_DELIM_CAPTURE,
                );

                $pCount = \count($M[1]);
            } else {
                $this->cache['urls'][] = $url;

                $pCount = 0;
            }

            foreach ($actions as $action) {
                if (\is_string($action)) {
                    $action = [$action, null];
                }

                foreach ([$action[0], basename(strtr($action[0], '\\', '/')), $action[1]] as $name) {
                    if ($name !== null) {
                        $this->cache['actions']["$name $pCount"] = \count($this->cache['urls']) - 1;
                    }
                }
            }
        }

        $this->cache['regex'] = sprintf('{^(?|%s)$}', implode('|', $this->cache['regex']));
    }
}
