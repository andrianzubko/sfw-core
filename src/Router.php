<?php
declare(strict_types=1);

namespace SFW;

/**
 * Abstraction for routers.
 */
abstract class Router extends Base
{
    /**
     * Gets action.
     */
    abstract public function getAction(): array|null|false;
}
