<?php

namespace SFW\Lazy\Sys;

/**
 * ABnormal END.
 */
class Abend extends \SFW\Lazy\Sys
{
    /**
     * Log error and show error page 500.
     */
    public function error(?string $message = null, ?string $file = null, ?int $line = null): void
    {
        $this->process(__FUNCTION__, $message, $file, $line);
    }

    /**
     * Just log error without exiting.
     */
    public function warn(?string $message = null, ?string $file = null, ?int $line = null): void
    {
        $this->process(__FUNCTION__, $message, $file, $line);
    }

    /**
     * Base method.
     */
    protected function process(string $mode, ?string $message, ?string $file, ?int $line): void
    {
        if (!isset($file, $line)) {
            foreach (debug_backtrace() as $trace) {
                if ($trace['file'] !== __FILE__) {
                    $file = $trace['file'];

                    $line = $trace['line'];

                    break;
                }
            }
        }

        if (isset($message)) {
            error_log(
                sprintf(
                    $mode === 'error'
                        ? '%s, stopped at %s line %s'
                        : '%s at %s line %s',

                    transliterator_transliterate('Any-Latin; Latin-ASCII', $message), $file, $line
                )
            );
        } else {
            error_log(
                sprintf(
                    $mode === 'error'
                        ? 'Stopped at %s line %s'
                        : 'Some problems at %s line %s',

                    $file, $line
                )
            );
        }

        $this->errorPage(500);
    }

    /**
     * Show error page and exit.
     */
    public function errorPage(int $status): void
    {
        if (php_sapi_name() !== 'cli' && !headers_sent() && !ob_get_length()) {
            include PUB_DIR . "/.bin/errors/$status.php";
        }

        exit;
    }
}
