<?php

namespace SFW\Lazy;

/**
 * Logger.
 */
class Logger extends \SFW\Lazy
{
    /**
     * Just in case.
     */
    public function __construct() {}

    /**
     * Logging in time with default timezone.
     */
    public function save(string $file, string $message): void
    {
        $timezonePrev = date_default_timezone_get();

        if ($timezonePrev === self::$e['config']['timezone']) {
            $timezonePrev = null;
        } else {
            date_default_timezone_set(self::$e['config']['timezone']);
        }

        $message = sprintf("[%s] %s\n", date('d.m.y H:i'), $message);

        $this->file()->put($file, $message, FILE_APPEND);

        if (isset($timezonePrev)) {
            date_default_timezone_set($timezonePrev);
        }
    }
}
