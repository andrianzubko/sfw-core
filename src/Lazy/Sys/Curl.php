<?php

namespace SFW\Lazy\Sys;

/**
 * CURL.
 */
class Curl extends \SFW\Lazy\Sys
{
    /**
     * Do CURL request with encoding detection.
     */
    public function request(array $options, ?array &$headers = null): string|false
    {
        $options[CURLOPT_RETURNTRANSFER] = true;

        $options[CURLOPT_SSL_VERIFYPEER] = false;

        $options[CURLOPT_HEADER] = true;

        if (!isset($options[CURLOPT_FOLLOWLOCATION])) {
            $options[CURLOPT_FOLLOWLOCATION] = true;
        }

        $curl = curl_init();

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        $info = curl_getinfo($curl);

        curl_close($curl);

        $headers = [];

        if ($response === false) {
            return false;
        }

        $headers = preg_split('/\r\n\r\n/', substr($response, 0, $info['header_size']), 0, PREG_SPLIT_NO_EMPTY);

        if (!count($headers)) {
            return false;
        }

        $headers = preg_split('/\r\n/', array_pop($headers), 0, PREG_SPLIT_NO_EMPTY);

        if ($info['header_size'] >= strlen($response)) {
            return '';
        }

        $response = substr($response, $info['header_size']);

        if (mb_check_encoding($response) || !preg_match('~text/~i', $info['content_type'])) {
            return $response;
        }

        $encoding = 'utf-8';

        if ((preg_match('~charset\s*=\s*([a-z\d\-]+)~i', $info['content_type'], $M)
            || preg_match('~text/html~i', $info['content_type'])
                && preg_match('~<meta[^>]+content-type[^>]*>~i', $response, $N)
                    && preg_match('~charset\s*=\s*([a-z\d\-]+)~i', $N[0], $M))
                        && !in_array(strtolower($M[1]), ['utf8','utf-8'], true)
                            && @mb_encoding_aliases($M[1]) !== false
        ) {
            $encoding = $M[1];
        }

        return @mb_convert_encoding($response, 'utf-8', $encoding);
    }
}
