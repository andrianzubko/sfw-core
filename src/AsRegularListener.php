<?php

namespace SFW;

/**
 * Registers regular listener.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsRegularListener extends AsListener
{
}
