<?php

namespace SFW\Config;

/**
 * Extended configuration available from everywhere.
 */
class Extend
{
    /**
     * Basic URL of site (autodetect if not set).
     */
    public ?string $basicUrl = null;

    /**
     * Default timezone.
     */
    public string $timezone = 'Europe/Moscow';
}
