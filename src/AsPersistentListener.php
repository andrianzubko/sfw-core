<?php

declare(strict_types=1);

namespace SFW;

/**
 * Registers persistent listener (can be called many times and can only be removed with the force parameter).
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsPersistentListener extends AsSomeListener {}
