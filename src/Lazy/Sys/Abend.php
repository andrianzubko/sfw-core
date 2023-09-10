<?php

namespace SFW\Lazy\Sys;

/**
 * ABnormal END.
 */
class Abend extends \SFW\Lazy\Sys
{
    /**
     * Error document optional file pattern.
     */
    protected string $errorDocument = APP_DIR . '/public/.bin/errors/%s.php';

    /**
     * Log error and show error page 500.
     */
    public function error(
        \Exception|string|null $message = null,
        ?string $file = null,
        ?int $line = null
    ): void {
        $this->process(__FUNCTION__, $message, $file, $line);
    }

    /**
     * Just log error without exiting.
     */
    public function warn(
        \Exception|string|null $message = null,
        ?string $file = null,
        ?int $line = null
    ): void {
        $this->process(__FUNCTION__, $message, $file, $line);
    }

    /**
     * Base method.
     */
    protected function process(
        string $level,
        \Exception|string|null $message,
        ?string $file,
        ?int $line
    ): void {
        if ($message instanceof \Exception) {
            $lines = [];

            $lines[] = sprintf(
                $level === 'error'
                    ? '%s, stopped in %s:%s'
                    : '%s in %s:%s',

                transliterator_transliterate('Any-Latin; Latin-ASCII',
                    $message->getMessage()
                ),
                $message->getFile(),
                $message->getLine()
            );

            $lines[] = 'Stack trace:';

            foreach ($message->getTrace() as $i => $trace) {
                $lines[] = sprintf('#%d %s:%d: %s%s%s()',
                    $i,
                    $trace['file'],
                    $trace['line'],
                    $trace['class'],
                    $trace['type'],
                    $trace['function']
                );
            }

            $message = implode("\n", $lines);
        } else {
            if (isset($message)
                && $level === 'error'
            ) {
                $message = sprintf('%s, stopped',
                    transliterator_transliterate('Any-Latin; Latin-ASCII',
                        $message
                    )
                );
            } elseif (isset($message)) {
                $message = transliterator_transliterate('Any-Latin; Latin-ASCII',
                    $message
                );
            } elseif ($level === 'error') {
                $message = 'Stopped';
            } else {
                $message = 'Some problems';
            }

            if (!isset($file, $line)) {
                foreach (debug_backtrace() as $trace) {
                    if ($trace['file'] !== __FILE__) {
                        $file = $trace['file'];

                        $line = $trace['line'];

                        break;
                    }
                }
            }

            $message = sprintf('%s in %s:%s', $message, $file, $line);
        }

        error_log($message);

        if ($level === 'error') {
            $this->errorPage(500);
        }
    }

    /**
     * Set error header and exit.
     */
    public function errorPage(int $code): void
    {
        if (PHP_SAPI !== 'cli'
            && !headers_sent()
            && !ob_get_length()
        ) {
            http_response_code($code);

            $errorDocument = sprintf($this->errorDocument, $code);

            if (is_file($errorDocument)) {
                include $errorDocument;
            }
        }

        exit;
    }
}
