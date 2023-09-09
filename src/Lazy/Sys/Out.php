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
     * Used template processor.
     */
    protected string $templater = 'Templater';

    /**
     * Output string as attachment.
     */
    public function attachment(
        string $contents,
        string $mime = 'text/plain',
        int $expire = 0,
        ?string $filename = null,
        int $code = 200
    ): self {
        return $this->put('attachment', $contents, $mime, $expire, $filename, $code);
    }

    /**
     * Output string as inline.
     */
    public function inline(
        string $contents,
        string $mime = 'text/plain',
        int $expire = 0,
        ?string $filename = null,
        int $code = 200
    ): self {
        return $this->put('inline', $contents, $mime, $expire, $filename, $code);
    }

    /**
     * Output some as inline json.
     */
    public function json(
        mixed $contents,
        string $mime = 'text/plain',
        int $expire = 0,
        ?string $filename = null,
        int $code = 200
    ): self {
        return $this->put('inline', json_encode($contents), $mime, $expire, $filename, $code);
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
        int $code
    ): self {
        ini_set('zlib.output_compression', false);

        http_response_code($code);

        header(
            sprintf('Last-Modified: %s',
                gmdate('D, d M Y H:i:s \G\M\T',
                    self::$e['sys']['timestamp']
                )
            )
        );

        header("Cache-Control: private, max-age=$expire");

        header("Content-Type: $mime; charset=utf-8");

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
            header("Content-Disposition: $disposition; filename=\"$filename\"");
        } elseif (
            $disposition === 'attachment'
        ) {
            header('Content-Disposition: attachment');
        }

        if (!function_exists('fastcgi_finish_request')) {
            header('Connection: close');
        }

        echo $contents;

        while (ob_get_length()) {
            ob_end_flush();
        }

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
        int $code = 200
    ): string|self {
        try {
            $contents = $this->sys($this->templater)->transform($e, $template);
        } catch (
            \SFW\Templater\Exception $error
        ) {
            foreach (debug_backtrace() as $trace) {
                if ($trace['file'] !== __FILE__) {
                    $this->sys('Abend')->error(
                        $error->getMessage(),
                        $trace['file'],
                        $trace['line']
                    );
                }
            }
        }

        if ($toString) {
            return $contents;
        }

        if (self::$config['sys']['templater']['stats']) {
            $timer = gettimeofday(true) - self::$startedTime;

            $contents .= sprintf(
                '<!-- script %.03f + sql(%s) %.03f + template(%s) %.03f = %.03f -->',
                    $timer - $this->sys('Db')->getTimer() - $this->sys('Templater')->getTimer(),
                    $this->sys('Db')->getCounter(),
                    $this->sys('Db')->getTimer(),
                    $this->sys('Templater')->getCounter(),
                    $this->sys('Templater')->getTimer(),
                    $timer
            );
        }

        $this->inline($contents, 'text/html', code: $code);

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

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $url = self::$e['sys']['url'] . $url;
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

    /**
     * Sets some options.
     *
     * @internal
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $option) {
            if ($option === 'Native' || $option === 'Xslt') {
                $this->templater = $option;
            } else {
                $this->sys('Abend')->error("Unknown option $option");
            }
        }
    }
}
