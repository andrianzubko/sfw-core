<?php

namespace SFW;

/**
 * Simplest framework.
 */
class Runner extends Base
{
    /**
     * Initializing environment and routing to starting point.
     */
    public function __construct(array $namespaces = [])
    {
        $namespaces = array_merge(['Config\\', 'Lazy\\', 'Point\\'], $namespaces);

        spl_autoload_register(
            function (string $class) use ($namespaces): void {
                $path = str_replace('\\', '/', $class);

                foreach ($namespaces as $namespace) {
                    if (str_starts_with($class, $namespace)) {
                        if (is_file("src/$path.php")) {
                            require "src/$path.php";
                        }

                        break;
                    }
                }
            }, true, true
        );

        $this->sys()->setDefaultEnvironment();

        $this->sys()->setExtraEnvironment();

        $point = self::$e['system']['point'];

        if ($point === false || !class_exists("Point\\$point")) {
            $this->abend()->errorPage(404);
        }

        new ("Point\\$point")();
    }
}
