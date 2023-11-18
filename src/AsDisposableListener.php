<?php

declare(strict_types=1);

namespace SFW;

/**
 * Registers disposable listener (can be called only once).
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsDisposableListener extends AsListener {}
