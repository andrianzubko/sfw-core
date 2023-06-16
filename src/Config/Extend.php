<?php

namespace SFW\Config;

/**
 * Extended configuration available from everywhere.
 */
class Extend
{
    // {{{ basic

    /**
     * Basic URL of site.
     */
    public ?string $basicUrl = null;

    /**
     * Default timezone.
     */
    public string $timezone = 'Europe/Moscow';

    /**
     * Sitename.
     */
    public string $sitename = '';

    /**
     * Opened for robots or not.
     */
    public bool $robots = false;

    // }}}
}
