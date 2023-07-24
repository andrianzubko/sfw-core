<?php

namespace SFW\Lazy\Sys;

/**
 * Output control.
 */
class Out extends \SFW\Lazy\Sys
{
    /**
     * Mime types for compress via gzip.
     */
    protected array $compress = [
        'text/html',
        'text/plain',
        'text/xml',
        'text/css',
        'application/x-javascript',
        'application/javascript',
        'application/ecmascript',
        'application/rss+xml',
        'application/xml',
    ];

    /**
     * Output string as attachment.
     */
    public function attachment(
        string $contents,
        string $mime = 'text/plain',
        int $expire = 0,
        ?string $filename = null,
        int $status = 200
    ): self {
        return $this->put('attachment', $contents, $mime, $expire, $filename, $status);
    }

    /**
     * Output string as inline.
     */
    public function inline(
        string $contents,
        string $mime = 'text/plain',
        int $expire = 0,
        ?string $filename = null,
        int $status = 200
    ): self {
        return $this->put('inline', $contents, $mime, $expire, $filename, $status);
    }

    /**
     * Output some as inline json.
     */
    public function json(
        mixed $contents,
        string $mime = 'text/plain',
        int $expire = 0,
        ?string $filename = null,
        int $status = 200
    ): self {
        return $this->put('inline', json_encode($contents), $mime, $expire, $filename, $status);
    }

    /**
     * Output base.
     */
    protected function put(
        string $disposition,
        string $contents,
        string $mime,
        int $expire,
        ?string $filename,
        int $status
    ): self {
        ini_set('zlib.output_compression', false);

        http_response_code($status);

        header(
            sprintf('Last-Modified: %s',
                gmdate('D, d M Y H:i:s \G\M\T', self::$e['defaults']['timestamp'])
            )
        );

        header(
            sprintf('Cache-Control: private, max-age=%s', $expire)
        );

        header(
            sprintf('Content-Type: %s; charset=utf-8', $mime)
        );

        if (strlen($contents) > 32 * 1024
            && in_array($mime, $this->compress, true)
            && str_contains($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip')
        ) {
            header('Content-Encoding: gzip');

            $contents = gzencode($contents, 5);
        } else {
            header('Content-Encoding: none');
        }

        header(
            sprintf('Content-Length: %s',
                strlen($contents)
            )
        );

        if (isset($filename)) {
            header(
                sprintf('Content-Disposition: %s; filename="%s"',
                    $disposition, $filename
                )
            );
        } elseif ($disposition === 'attachment') {
            header('Content-Disposition: attachment');
        }

        if (!function_exists('fastcgi_finish_request')) {
            header('Connection: close');
        }

        echo $contents;

        ob_end_flush();

        flush();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        return $this;
    }

    /**
     * Process and output template.
     */
    public function template(
        array $e,
        string $template,
        bool $toString = false,
        int $status = 200
    ): string|self {
        $contents = $this->sys('Templater')->transform($e, $template);

        if ($toString) {
            return $contents;
        }

        if (self::$config['sys']['templater']['stats']) {
            $timer = gettimeofday(true) - self::$startedTime;

            $dbTimer = $dbCounter = 0;

            $dbDrivers = [];

            if (isset(self::$sysLazyInstances)) {
                foreach (self::$sysLazyInstances as $lazy) {
                    if ($lazy instanceof \SFW\Databaser\Driver
                        && !in_array($lazy, $dbDrivers, true)
                    ) {
                        $dbTimer += $lazy->getTimer();

                        $dbCounter += $lazy->getCounter();

                        $dbDrivers[] = $lazy;
                    }
                }
            }

            $contents .= sprintf(
                "\n<!-- script %.03f + sql(%s) %.03f + template(%s) %.03f = %.03f -->",
                    $timer - $dbTimer - $this->sys('Templater')->getTimer(),
                    $dbCounter,
                    $dbTimer,
                    $this->sys('Templater')->getCounter(),
                    $this->sys('Templater')->getTimer(),
                    $timer
            );
        }

        $this->inline($contents, 'text/html', status: $status);

        return $this;
    }

    /**
     * Redirect.
     */
    public function redirect(string $url): void
    {
        if ($url === '') {
            $url = '/';
        }

        if (str_starts_with($url, '/')
            && !str_starts_with($url, '//')
        ) {
            $url = self::$e['defaults']['url'] . $url;
        }

        http_response_code(302);

        header("Location: $url");

        exit;
    }

    /**
     * Just exit for fluent syntax.
     */
    public function end(): void
    {
        exit;
    }
}
