<?php

namespace SFW\Lazy\Sys;

use SFW\Exception\Logic;

/**
 * Response.
 */
class Response extends \SFW\Lazy\Sys
{
    /**
     * Exit after any response.
     */
    protected bool $exit = true;

    /**
     * Process and output native template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws Logic
     * @throws \SFW\Templater\Exception
     */
    public function native(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        int $code = 200,
        int $expire = 0,
    ): void {
        $this->transform('Native', $filename, $context, $mime, $code, $expire);
    }

    /**
     * Process and output twig template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws Logic
     * @throws \SFW\Templater\Exception
     */
    public function twig(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        int $code = 200,
        int $expire = 0,
    ): void {
        $this->transform('Twig', $filename, $context, $mime, $code, $expire);
    }

    /**
     * Process and output xslt template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws Logic
     * @throws \SFW\Templater\Exception
     */
    public function xslt(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        int $code = 200,
        int $expire = 0,
    ): void {
        $this->transform('Xslt', $filename, $context, $mime, $code, $expire);
    }

    /**
     * Process and output template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws Logic
     * @throws \SFW\Templater\Exception
     */
    public function template(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        int $code = 200,
        int $expire = 0,
    ): void {
        $this->transform('Templater', $filename, $context, $mime, $code, $expire);
    }

    /**
     * Base method for template transformation.
     *
     * @throws Logic
     * @throws \SFW\Templater\Exception
     */
    protected function transform(
        string $processor,
        string $filename,
        array|object|null $context,
        ?string $mime,
        int $code,
        int $expire,
    ): void {
        $contents = self::sys($processor)->transform($filename, $context);

        $mime ??= self::sys($processor)->getMime();

        if (self::$sys['config']['response_stats'] !== null && $mime === 'text/html') {
            $timer = gettimeofday(true) - self::$sys['started'];

            $contents .= str_replace(
                [
                    '{SCR_T}',
                    '{SQL_C}',
                    '{SQL_T}',
                    '{TPL_C}',
                    '{TPL_T}',
                    '{ALL_T}',
                ], [
                    sprintf('%.3f', $timer - self::sys('Db')->getTimer() - self::sys('Templater')->getTimer()),
                    self::sys('Db')->getCounter(),
                    sprintf('%.3f', self::sys('Db')->getTimer()),
                    self::sys('Templater')->getCounter(),
                    sprintf('%.3f', self::sys('Templater')->getTimer()),
                    sprintf('%.3f', $timer),
                ], self::$sys['config']['response_stats']
            );
        }

        $this->inline($contents, $mime, $code, $expire);
    }

    /**
     * Output json as inline.
     *
     * @throws Logic
     */
    public function json(
        mixed $contents,
        bool $pretty = false,
        int $code = 200,
        int $expire = 0,
    ): void {
        $contents = json_encode($contents,
            $pretty ? JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT : JSON_UNESCAPED_UNICODE
        );

        $this->output('inline', $contents, 'application/json', $code, $expire, null);
    }

    /**
     * Output contents as inline.
     *
     * @throws Logic
     */
    public function inline(
        string $contents,
        string $mime = 'text/plain',
        int $code = 200,
        int $expire = 0,
        ?string $filename = null,
    ): void {
        $this->output('inline', $contents, $mime, $code, $expire, $filename);
    }

    /**
     * Output contents as attachment.
     *
     * @throws Logic
     */
    public function attachment(
        string $contents,
        string $mime = 'text/plain',
        int $code = 200,
        int $expire = 0,
        ?string $filename = null,
    ): void {
        $this->output('attachment', $contents, $mime, $code, $expire, $filename);
    }

    /**
     * Base method for outputs.
     *
     * @throws Logic
     */
    protected function output(
        string $disposition,
        string $contents,
        string $mime,
        int $code,
        int $expire,
        ?string $filename,
    ): void {
        if (headers_sent()) {
            throw new Logic('Headers already sent');
        }

        ini_set('zlib.output_compression', false);

        http_response_code($code);

        header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', self::$sys['timestamp']));

        header("Cache-Control: private, max-age=$expire");

        header("Content-Type: $mime; charset=utf-8");

        if ($filename !== null) {
            header("Content-Disposition: $disposition; filename=\"$filename\"");
        } else {
            header("Content-Disposition: $disposition");
        }

        $compressMimes = self::$sys['config']['response_compress_mimes'];

        $compressMin = self::$sys['config']['response_compress_min'];

        if (isset($compressMimes, $_SERVER['HTTP_ACCEPT_ENCODING'])
            && \strlen($contents) > $compressMin
                && \in_array($mime, $compressMimes, true)
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

        header('Content-Length: ' . \strlen($contents));

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

        if ($this->exit) {
            exit(0);
        }
    }

    /**
     * Redirect.
     *
     * @throws Logic
     */
    public function redirect(string $uri, int $code = 302): void
    {
        if (headers_sent()) {
            throw new Logic('Headers already sent');
        }

        header("Location: $uri", response_code: $code);

        if ($this->exit) {
            exit(0);
        }
    }

    /**
     * Shows error page.
     */
    public function error(int $code): void
    {
        if (!headers_sent() && !ob_get_length()) {
            http_response_code($code);

            $errorDocument = self::$sys['config']['response_error_document'];

            if ($errorDocument !== null) {
                $errorDocument = str_replace('{CODE}', $code, $errorDocument);

                if (is_file($errorDocument)) {
                    include $errorDocument;
                }
            }
        }

        if ($this->exit) {
            exit(1);
        }
    }

    /**
     * For prevents exit after response.
     */
    public function exit(bool $exit): self
    {
        $this->exit = $exit;

        return $this;
    }
}
