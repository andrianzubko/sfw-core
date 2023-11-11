<?php

declare(strict_types=1);

namespace SFW;

/**
 * Registers listener (can be called many times).
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsListener extends AsSomeListener {}
