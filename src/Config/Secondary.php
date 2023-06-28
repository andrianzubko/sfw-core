<?php

namespace SFW\Config;

/**
 * Secondary configuration available from everywhere.
 */
class Secondary
{
    /**
     * Basic URL of site (autodetect if not set).
     */
    public ?string $basic_url = null;

    /**
     * Default timezone.
     */
    public string $timezone = 'Europe/Moscow';
}
