<?php
declare(strict_types=1);

namespace SFW\Registry;

/**
 * Registry of listeners.
 */
final class Commands extends \SFW\Registry
{
    /**
     * Checks and actualize cache if needed.
     *
     * @throws \SFW\Exception\Runtime
     */
    public function __construct()
    {
        parent::__construct(self::$sys['config']['commands_cache']);
    }

    /**
     * Rebuilds cache.
     *
     * @throws \SFW\Exception\Runtime
     */
    protected function rebuildCache(): void
    {
        $this->cache = [];

        $this->cache['commands'] = [];

        foreach (get_declared_classes() as $class) {
            if (!str_starts_with($class, 'App\\')) {
                continue;
            }

            $rClass = new \ReflectionClass($class);

            foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $rMethod) {
                foreach ($rMethod->getAttributes(\SFW\AsCommand::class) as $rAttribute) {
                    if ($rMethod->isConstructor()) {
                        self::sys('Logger')->warning("Constructor can't be a command", options: [
                            'file' => $rMethod->getFileName(),
                            'line' => $rMethod->getStartLine()
                        ]);

                        continue;
                    }

                    $name = strtolower(
                        implode(':', [
                            $rClass->getShortName(), ...preg_split('/(?=[A-Z])/', $rMethod->name)
                        ])
                    );

                    $this->cache['commands'][$name] = "$class::$rMethod->name";
                }
            }
        }
    }
}
