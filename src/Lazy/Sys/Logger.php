<?php

namespace SFW\Lazy\Sys;

/**
 * Logger.
 */
class Logger extends \SFW\Lazy\Sys
{
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

        self::$sys->file()->put($file, $message, FILE_APPEND);

        if (isset($timezonePrev)) {
            date_default_timezone_set($timezonePrev);
        }
    }
}
