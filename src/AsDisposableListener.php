<?php

namespace SFW;

/**
 * Registers disposable listener.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsDisposableListener extends AsListener
{
}
