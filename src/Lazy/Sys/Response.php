<?php

namespace SFW\Lazy\Sys;

/**
 * Response.
 */
class Response extends \SFW\Lazy\Sys
{
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
        int $code = 200,
        int $expire = 0,
    ): self {
        return $this->transform('Native', $filename, $context, $mime, $code, $expire);
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
        int $code = 200,
        int $expire = 0,
    ): self {
        return $this->transform('Twig', $filename, $context, $mime, $code, $expire);
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
        int $code = 200,
        int $expire = 0,
    ): self {
        return $this->transform('Xslt', $filename, $context, $mime, $code, $expire);
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
        int $code = 200,
        int $expire = 0,
    ): self {
        return $this->transform('Templater', $filename, $context, $mime, $code, $expire);
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
        int $code,
        int $expire,
    ): self {
        $contents = self::sys($processor)->transform($filename, $context);

        $mime ??= self::sys($processor)->getMime();

        if (isset(self::$config['sys']['response']['stats'])
            && $mime === 'text/html'
        ) {
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
                    sprintf('%.3f',
                        $timer - self::sys('Db')->getTimer() - self::sys('Templater')->getTimer()
                    ),
                    self::sys('Db')->getCounter(),
                    sprintf('%.3f',
                        self::sys('Db')->getTimer()
                    ),
                    self::sys('Templater')->getCounter(),
                    sprintf('%.3f',
                        self::sys('Templater')->getTimer()
                    ),
                    sprintf('%.3f',
                        $timer
                    ),
                ], self::$config['sys']['response']['stats']
            );
        }

        return $this->inline($contents, $mime, $code, $expire);
    }

    /**
     * Output json as inline.
     */
    public function json(
        mixed $contents,
        bool $pretty = false,
        int $code = 200,
        int $expire = 0,
    ): self {
        return $this->output('inline',
            json_encode($contents,
                $pretty
                    ? JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
                    : JSON_UNESCAPED_UNICODE
            ), 'application/json', $code, $expire, null
        );
    }

    /**
     * Output contents as inline.
     */
    public function inline(
        string $contents,
        string $mime = 'text/plain',
        int $code = 200,
        int $expire = 0,
        ?string $filename = null,
    ): self {
        return $this->output('inline', $contents, $mime, $code, $expire, $filename);
    }

    /**
     * Output contents as attachment.
     */
    public function attachment(
        string $contents,
        string $mime = 'text/plain',
        int $code = 200,
        int $expire = 0,
        ?string $filename = null,
    ): self {
        return $this->output('attachment', $contents, $mime, $code, $expire, $filename);
    }

    /**
     * Base method for outputs.
     */
    protected function output(
        string $disposition,
        string $contents,
        string $mime,
        int $code,
        int $expire,
        ?string $filename,
    ): self {
        ini_set('zlib.output_compression', false);

        http_response_code($code);

        header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', self::$sys['timestamp']));

        header("Cache-Control: private, max-age=$expire");

        header("Content-Type: $mime; charset=utf-8");

        if (isset($filename)) {
            header("Content-Disposition: $disposition; filename=\"$filename\"");
        } else {
            header("Content-Disposition: $disposition");
        }

        $compressMimes = self::$config['sys']['response']['compress_mimes'];

        $compressMin = self::$config['sys']['response']['compress_min'];

        if (isset($compressMimes, $_SERVER['HTTP_ACCEPT_ENCODING'])
            && strlen($contents) > $compressMin
                && in_array($mime, $compressMimes, true)
                    && preg_match('/(deflate|gzip)/', $_SERVER['HTTP_ACCEPT_ENCODING'], $M)
        ) {
            if ($M[1] === 'gzip') {
                $contents = gzencode($contents, 1);
            } else {
                $contents = gzdeflate($contents, 1);
            }

            header("Content-Encoding: $M[1]");

            header('Vary: Accept-Encoding');
        } else {
            header('Content-Encoding: none');
        }

        header('Content-Length: ' . strlen($contents));

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
     * Redirect.
     */
    public function redirect(string $uri, int $code = 302, bool $exit = true): self
    {
        header("Location: $uri", response_code: $code);

        if ($exit) {
            exit(0);
        }

        return $this;
    }

    /**
     * Shows error page.
     */
    public function error(int $code, bool $exit = true): self
    {
        if (!headers_sent() && !ob_get_length()) {
            http_response_code($code);

            $errorDocument = self::$config['sys']['response']['error_document'];

            if (isset($errorDocument)) {
                $errorDocument = str_replace('{CODE}', $code, $errorDocument);

                if (is_file($errorDocument)) {
                    include $errorDocument;
                }
            }
        }

        if ($exit) {
            exit(1);
        }

        return $this;
    }

    /**
     * Just exit for fluent syntax.
     */
    public function exit(string|int $status = 0): void
    {
        exit($status);
    }
}
