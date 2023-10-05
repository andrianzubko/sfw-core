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
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * Output some as inline pretty print json.
     */
    public function json(
        mixed $contents,
        string $mime = 'text/plain',
        ?string $filename = null,
        int $expire = 0,
        int $code = 200,
        bool $pretty = false
    ): self {
        return $this->inline(
            json_encode($contents,
                $pretty
                    ? JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
                    : JSON_UNESCAPED_UNICODE
            ), $mime, $filename, $expire, $code
        );
    }

    /**
     * Output string as inline.
     */
    public function inline(
        string $contents,
        string $mime = 'text/plain',
        ?string $filename = null,
        int $expire = 0,
        int $code = 200
    ): self {
        return $this->output('inline', $contents, $mime, $filename, $expire, $code);
    }

    /**
     * Output string as attachment.
     */
    public function attachment(
        string $contents,
        string $mime = 'text/plain',
        ?string $filename = null,
        int $expire = 0,
        int $code = 200
    ): self {
        return $this->output('attachment', $contents, $mime, $filename, $expire, $code);
    }

    /**
     * Base method for outputs.
     */
    protected function output(
        string $disposition,
        string $contents,
        string $mime,
        ?string $filename,
        int $expire,
        int $code
    ): self {
        ini_set('zlib.output_compression', false);

        http_response_code($code);

        header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', self::$sys['timestamp']));

        header("Cache-Control: private, max-age=$expire");

        header("Content-Type: $mime; charset=utf-8");

        if (strlen($contents) > 32 * 1024
            && in_array($mime, $this->compress, true)
                && str_contains($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip')
        ) {
            header('Content-Encoding: gzip');

            $contents = gzencode($contents, 1);
        } else {
            header('Content-Encoding: none');
        }

        header('Content-Length: ' . strlen($contents));

        if (isset($filename)) {
            header("Content-Disposition: $disposition; filename=\"$filename\"");
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
     * Process and output native template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws \SFW\Templater\Exception
     */
    public function native(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        int $code = 200
    ): self {
        return $this->transform('Native', $filename, $context, $mime, $code);
    }

    /**
     * Process and output twig template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws \SFW\Templater\Exception
     */
    public function twig(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        int $code = 200
    ): self {
        return $this->transform('Twig', $filename, $context, $mime, $code);
    }

    /**
     * Process and output xslt template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws \SFW\Templater\Exception
     */
    public function xslt(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        int $code = 200
    ): self {
        return $this->transform('Xslt', $filename, $context, $mime, $code);
    }

    /**
     * Process and output template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws \SFW\Templater\Exception
     */
    public function template(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        int $code = 200
    ): self {
        return $this->transform('Templater', $filename, $context, $mime, $code);
    }

    /**
     * Base method for template transformation.
     *
     * @throws \SFW\Templater\Exception
     */
    protected function transform(
        string $processor,
        string $filename,
        array|object|null $context,
        ?string $mime,
        int $code
    ): self {
        $contents = $this->sys($processor)->transform($filename, $context);

        if (!isset($mime)) {
            $mime = $this->sys($processor)->getLastMime();
        }

        if (isset(self::$config['sys']['response']['stats'])
            && $mime === 'text/html'
        ) {
            $contents .= $this->makeStats();
        }

        return $this->inline($contents, $mime, code: $code);
    }

    /**
     * Makes statistics line.
     *
     * Note: no checks for statistics pattern existence!
     */
    protected function makeStats(): string
    {
        $timer = gettimeofday(true) - self::$startedTime;

        return str_replace(
            [
                '{SCR_T}',
                '{SQL_C}',
                '{SQL_T}',
                '{TPL_C}',
                '{TPL_T}',
                '{ALL_T}',
            ], [
                sprintf('%.3f',
                    $timer - $this->sys('Db')->getTimer() - $this->sys('Templater')->getTimer()
                ),
                $this->sys('Db')->getCounter(),
                sprintf('%.3f',
                    $this->sys('Db')->getTimer()
                ),
                $this->sys('Templater')->getCounter(),
                sprintf('%.3f',
                    $this->sys('Templater')->getTimer()
                ),
                sprintf('%.3f',
                    $timer
                ),
            ], self::$config['sys']['response']['stats']
        );
    }

    /**
     * Shows error page.
     */
    public function errorPage(int $code, $exit = true): self
    {
        if (!headers_sent() && !ob_get_length()) {
            http_response_code($code);

            if (isset(self::$config['sys']['response']['error_document'])) {
                $errorDocument = str_replace('{CODE}', $code,
                    self::$config['sys']['response']['error_document']
                );

                if (is_file($errorDocument)) {
                    include $errorDocument;
                }
            }
        }

        if ($exit) {
            $this->exit(1);
        }

        return $this;
    }

    /**
     * Redirect.
     */
    public function redirect(string $url, $exit = true): self
    {
        if ($url === '') {
            $url = self::$sys['url'] . '/';
        } elseif (
                str_starts_with($url, '/')
            && !str_starts_with($url, '//')
        ) {
            $url = self::$sys['url'] . $url;
        }

        http_response_code(302);

        header("Location: $url");

        if ($exit) {
            $this->exit();
        }

        return $this;
    }
}
