<?php

namespace SFW\Lazy;

/**
 * Logger.
 */
class Logger extends \SFW\Lazy
{
    /**
     * Logging in default timezone time.
     */
    public function save(string $file, string $message): void
    {
        $timezonePrev = date_default_timezone_get();

        if ($timezonePrev === self::$e['config']['timezone']) {
            $timezonePrev = null;
        } else {
            date_default_timezone_set(self::$e['config']['timezone']);
        }

        $this->file()->put($file,
            sprintf("[%s] %s\n", date('d.m.y H:i'), $message)
        );

        if (isset($timezonePrev)) {
            date_default_timezone_set($timezonePrev);
        }
    }
}
