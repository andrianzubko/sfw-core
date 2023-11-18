<?php

declare(strict_types=1);

namespace SFW\Router;

use SFW\AsCommand;
use SFW\Exception;
use SFW\Router;

/**
 * Commands router.
 */
final class Command extends Router
{
    /**
     * Internal cache.
     */
    protected static array|false $cache;

    /**
     * Reads and actualizes cache if needed.
     *
     * @throws Exception\Runtime
     */
    public function __construct()
    {
        if (!isset(self::$cache)) {
            $this->readCache(self::$sys['config']['router_commands_cache']);
        }
    }

    /**
     * Gets current action.
     *
     * Very poor implementation. Will be better soon.
     */
    public function getCurrentAction(): array|null|false
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

        $action['class'] = strtok($action['short'], '::');

        $action['alias'] = null;

        return $action;
    }

    /**
     * Rebuilds cache.
     */
    protected function rebuildCache(array $initialCache): void
    {
        self::$cache = $initialCache;

        self::$cache['commands'] = [];

        foreach (get_declared_classes() as $class) {
            if (!str_starts_with($class, 'App\\')) {
                continue;
            }

            $rClass = new \ReflectionClass($class);

            foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($rMethod->getAttributes(AsCommand::class) as $rAttribute) {
                    if ($rMethod->isConstructor()) {
                        self::sys('Logger')->warning("Constructor can't be a command", options: [
                            'file' => $rMethod->getFileName(),
                            'line' => $rMethod->getStartLine(),
                        ]);

                        continue;
                    }

                    $name = strtolower(
                        implode(':', [
                            $rClass->getShortName(),
                            ...preg_split('/(?=[A-Z])/', $rMethod->name),
                        ]),
                    );

                    self::$cache['commands'][$name] = "$class::$rMethod->name";
                }
            }
        }
    }
}
