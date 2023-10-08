<?php

namespace SFW\Lazy\Sys;

/**
 * Response.
 */
class Response extends \SFW\Lazy\Sys
{
    /**
     * Output some as inline pretty print json.
     *
     * @throws \SFW\LogicException
     */
    public function json(
        mixed $contents,
        string $mime = 'text/plain',
        ?string $filename = null,
        ?int $expire = null,
        ?int $code = null,
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
     *
     * @throws \SFW\LogicException
     */
    public function inline(
        string $contents,
        string $mime = 'text/plain',
        ?string $filename = null,
        ?int $expire = null,
        ?int $code = null,
    ): self {
        return $this->output('inline', $contents, $mime, $filename, $expire, $code);
    }

    /**
     * Output string as attachment.
     *
     * @throws \SFW\LogicException
     */
    public function attachment(
        string $contents,
        string $mime = 'text/plain',
        ?string $filename = null,
        ?int $expire = null,
        ?int $code = null,
    ): self {
        return $this->output('attachment', $contents, $mime, $filename, $expire, $code);
    }

    /**
     * Base method for outputs.
     *
     * @throws \SFW\LogicException
     */
    protected function output(
        string $disposition,
        string $contents,
        string $mime,
        ?string $filename,
        ?int $expire,
        ?int $code,
    ): self {
        if (headers_sent()) {
            throw new \SFW\LogicException('Headers already sent');
        }

        ini_set('zlib.output_compression', false);

        http_response_code($code ?? 200);

        header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', self::$sys['timestamp']));

        header('Cache-Control: private, max-age=' . ($expire ?? 0));

        header("Content-Type: $mime; charset=utf-8");

        if (isset($filename)) {
            header("Content-Disposition: $disposition; filename=\"$filename\"");
        } else {
            header("Content-Disposition: $disposition");
        }

        if (isset(self::$config['sys']['response']['compress_mimes'])
            && strlen($contents) > self::$config['sys']['response']['compress_min']
                && in_array($mime, self::$config['sys']['response']['compress_mimes'], true)
                    && preg_match('/(deflate|gzip)/', $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', $M)
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
     * Process and output native template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws \SFW\LogicException
     * @throws \SFW\Templater\Exception
     */
    public function native(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        ?int $code = null
    ): self {
        return $this->transform('Native', $filename, $context, $mime, $code);
    }

    /**
     * Process and output twig template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws \SFW\LogicException
     * @throws \SFW\Templater\Exception
     */
    public function twig(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        ?int $code = null
    ): self {
        return $this->transform('Twig', $filename, $context, $mime, $code);
    }

    /**
     * Process and output xslt template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws \SFW\LogicException
     * @throws \SFW\Templater\Exception
     */
    public function xslt(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        ?int $code = null
    ): self {
        return $this->transform('Xslt', $filename, $context, $mime, $code);
    }

    /**
     * Process and output template.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws \SFW\LogicException
     * @throws \SFW\Templater\Exception
     */
    public function template(
        string $filename,
        array|object|null $context = null,
        ?string $mime = null,
        ?int $code = null
    ): self {
        return $this->transform('Templater', $filename, $context, $mime, $code);
    }

    /**
     * Base method for template transformation.
     *
     * @throws \SFW\LogicException
     * @throws \SFW\Templater\Exception
     */
    protected function transform(
        string $processor,
        string $filename,
        array|object|null $context,
        ?string $mime,
        ?int $code
    ): self {
        $contents = $this->sys($processor)->transform($filename, $context);

        $mime ??= $this->sys($processor)->getMime();

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

        return $this->inline($contents, $mime, code: $code);
    }

    /**
     * Shows error page.
     */
    public function error(int $code, bool $exit = true): self
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
     *
     * @throws \SFW\LogicException
     */
    public function redirect(string $uri, ?int $code = null, bool $exit = true): self
    {
        if (headers_sent()) {
            throw new \SFW\LogicException('Headers already sent');
        }

        header("Location: $uri",
            response_code: $code ?? self::$config['sys']['response']['redirect_code']
        );

        if ($exit) {
            $this->exit();
        }

        return $this;
    }
}
