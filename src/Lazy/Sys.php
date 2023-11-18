<?php

declare(strict_types=1);

namespace SFW\Lazy;

use SFW\Lazy;

/**
 * Abstraction for system Lazy classes.
 */
abstract class Sys extends Lazy
{
    /**
     * Leave only allowed configuration parameters.
     *
     * @internal
     */
    protected function filterConfig(array $params): array
    {
        $params['config'] = array_intersect_key($params['config'], array_flip($params['config']['shared']));

        return $params;
    }
}
