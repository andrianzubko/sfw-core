<?php

declare(strict_types=1);

namespace SFW\Lazy\Sys;

/**
 * Curl.
 */
class Curl extends \SFW\Lazy\Sys
{
    /**
     * Last request info.
     */
    protected array $info = [];

    /**
     * Last request headers.
     */
    protected array $headers = [];

    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct() {}

    /**
     * Do Curl request with optional conversion to utf-8.
     */
    public function request(array $options, bool $toUtf8 = false): string|false
    {
        $options[CURLOPT_RETURNTRANSFER] = true;

        $options[CURLOPT_HEADER] = true;

        $options[CURLOPT_SSL_VERIFYPEER] ??= false;

        $options[CURLOPT_FOLLOWLOCATION] ??= true;

        $curl = curl_init();

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        $this->info = curl_getinfo($curl);

        curl_close($curl);

        $this->headers = [];

        if ($response === false) {
            return false;
        }

        $this->headers = preg_split('/\r\n\r\n/', substr($response, 0, $this->info['header_size']),
            flags: PREG_SPLIT_NO_EMPTY,
        );

        if (!$this->headers) {
            return false;
        }

        $this->headers = preg_split('/\r\n/', array_pop($this->headers), flags: PREG_SPLIT_NO_EMPTY);

        if ($this->info['header_size'] >= \strlen($response)) {
            return '';
        }

        $response = substr($response, $this->info['header_size']);

        if ($toUtf8 && !mb_check_encoding($response)) {
            if (preg_match('/charset\s*=\s*([a-z\d\-#]+)/i', $this->info['content_type'], $M)) {
                try {
                    return mb_convert_encoding($response, 'utf-8', $M[1]);
                } catch (\ValueError) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return $response;
    }

    /**
     * Gets last request info.
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * Gets last request headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
