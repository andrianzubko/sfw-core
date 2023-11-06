<?php
declare(strict_types=1);

namespace SFW;

/**
 * Registers disposable listener.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsDisposableListener extends AsSomeListener
{
}
