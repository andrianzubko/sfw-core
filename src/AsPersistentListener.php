<?php

namespace SFW;

/**
 * Registers persistent listener.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class AsPersistentListener extends AsListener
{
}