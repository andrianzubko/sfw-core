<?php
declare(strict_types=1);

namespace SFW;

/**
 * Registers listener.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsListener extends AsSomeListener
{
}
