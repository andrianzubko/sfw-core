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
        return $this->output(__FUNCTION__, $contents, $mime, $filename, $expire, $code);
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
        return $this->output(__FUNCTION__, $contents, $mime, $filename, $expire, $code);
    }

    /**
     * Output base.
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

        header(
            sprintf('Last-Modified: %s',
                gmdate('D, d M Y H:i:s \G\M\T', self::$sys['timestamp'])
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
        } elseif ($disposition === 'attachment') {
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
     * @throws \SFW\Templater\Exception
     */
    public function native(
        string $template,
        array|object|null $context = null,
        int $code = 200
    ): self {
        return $this->template($template, $context, $code, 'Native');
    }

    /**
     * Process and output twig template.
     *
     * @throws \SFW\Templater\Exception
     */
    public function twig(
        string $template,
        array|object|null $context = null,
        int $code = 200
    ): self {
        return $this->template($template, $context, $code, 'Twig');
    }

    /**
     * Process and output xslt template.
     *
     * @throws \SFW\Templater\Exception
     */
    public function xslt(
        string $template,
        array|object|null $context = null,
        int $code = 200
    ): self {
        return $this->template($template, $context, $code, 'Xslt');
    }

    /**
     * Process and output template.
     *
     * @throws \SFW\Templater\Exception
     */
    public function template(
        string $template,
        array|object|null $context = null,
        int $code = 200,
        string $processor = 'Templater'
    ): self {
        $contents = $this->sys($processor)->transform($template, $context);

        if (isset(self::$config['sys']['response']['stats'])) {
            if ($contents !== ''
                && !str_ends_with($contents, "\n")
            ) {
                $contents .= "\n";
            }

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
