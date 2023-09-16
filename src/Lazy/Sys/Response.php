<?php

namespace SFW\Lazy\Sys;

/**
 * Response.
 */
class Response extends \SFW\Lazy\Sys
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
     * Output some as inline json.
     */
    public function json(
        mixed $contents,
        string $mime = 'text/plain',
        int $expire = 0,
        ?string $filename = null,
        int $code = 200
    ): self {
        return $this->inline(json_encode($contents), $mime, $expire, $filename, $code);
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
        return $this->output(__FUNCTION__, $contents, $mime, $expire, $filename, $code);
    }

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
        return $this->output(__FUNCTION__, $contents, $mime, $expire, $filename, $code);
    }

    /**
     * Output base.
     */
    protected function output(
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
                gmdate('D, d M Y H:i:s \G\M\T', self::$e['sys']['timestamp'])
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
            sprintf('Content-Length: %s', strlen($contents))
        );

        if (isset($filename)) {
            header(
                sprintf(
                    'Content-Disposition: %s; filename="%s"',
                        $disposition,
                        $filename
                )
            );
        } elseif (
            $disposition === 'attachment'
        ) {
            header('Content-Disposition: attachment');
        }

        $ffrExists = function_exists('fastcgi_finish_request');

        if (!$ffrExists) {
            header('Connection: close');
        }

        echo $contents;

        while (ob_get_length()) {
            ob_end_flush();
        }

        flush();

        if ($ffrExists) {
            fastcgi_finish_request();
        }

        return $this;
    }

    /**
     * Process and output template.
     *
     * @throws \SFW\Templater\Exception
     */
    public function template(array $e, string $template, int $code = 200): string|self
    {
        $contents = $this->sys($this->templater)->transform($e, $template);

        if (isset(self::$config['sys']['response']['stats'])) {
            $timer = gettimeofday(true) - self::$startedTime;

            $contents .= str_replace(
                [
                    '{SCR_T}',
                    '{SQL_C}',
                    '{SQL_T}',
                    '{TPL_C}',
                    '{TPL_T}',
                    '{ALL_T}',
                ], [
                    sprintf('%.2f',
                        $timer - $this->sys('Db')->getTimer() - $this->sys('Templater')->getTimer()
                    ),
                    $this->sys('Db')->getCounter(),
                    sprintf('%.2f',
                        $this->sys('Db')->getTimer()
                    ),
                    $this->sys('Templater')->getCounter(),
                    sprintf('%.2f',
                        $this->sys('Templater')->getTimer()
                    ),
                    sprintf('%.2f',
                        $timer
                    ),
                ], self::$config['sys']['response']['stats']
            );
        }

        return $this->inline($contents, 'text/html', code: $code);
    }

    /**
     * Shows error page.
     */
    public function errorPage(int $code, $end = true): self
    {
        if (PHP_SAPI !== 'cli'
            && !headers_sent()
            && !ob_get_length()
        ) {
            http_response_code($code);

            $errorDocument = str_replace('{CODE}', $code,
                self::$config['sys']['response']['error_document']
            );

            if (is_file($errorDocument)) {
                include $errorDocument;
            }
        }

        if ($end) {
            $this->end();
        }

        return $this;
    }

    /**
     * Redirect.
     */
    public function redirect(string $url, $end = true): self
    {
        if ($url === '') {
            $url = '/';
        }

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $url = self::$e['sys']['url'] . $url;
        }

        http_response_code(302);

        header("Location: $url");

        if ($end) {
            $this->end();
        }

        return $this;
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
     * @throws \SFW\InvalidArgumentException
     *
     * @internal
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $option) {
            if ($option === 'Native' || $option === 'Xslt') {
                $this->templater = $option;
            } else {
                throw new \SFW\InvalidArgumentException("Unknown option $option");
            }
        }
    }
}
