<?php

namespace SFW\Lazy\Sys;

/**
 * Text functions.
 */
class Text extends \SFW\Lazy\Sys
{
    /**
     * UTF-8 spaces for trim.
     */
    protected string $spaces = " \t\n\r\0\x0B\x0C\u{A0}\u{FEFF}";

    /**
     * To lower case.
     */
    public function lc(?string $string): string
    {
        return mb_strtolower($string ?? '');
    }

    /**
     * First char to lower case.
     */
    public function lcfirst(?string $string): string
    {
        return mb_strtolower(mb_substr($string ?? '', 0, 1)) . mb_substr($string ?? '', 1);
    }

    /**
     * To upper case.
     */
    public function uc(?string $string): string
    {
        return mb_strtoupper($string ?? '');
    }

    /**
     * First char to upper case.
     */
    public function ucfirst(?string $string): string
    {
        return mb_strtoupper(mb_substr($string ?? '', 0, 1)) . mb_substr($string ?? '', 1);
    }

    /**
     * Trim both sides.
     */
    public function trim(?string $string): string
    {
        return trim($string ?? '', $this->spaces);
    }

    /**
     * Trim right side.
     */
    public function rtrim(?string $string): string
    {
        return rtrim($string ?? '', $this->spaces);
    }

    /**
     * Trim left side.
     */
    public function ltrim(string $string): string
    {
        return ltrim($string ?? '', $this->spaces);
    }

    /**
     * Trim both sides and convert all sequential spaces to one.
     */
    public function fulltrim(string $string, int $limit = 0): string
    {
        $string = preg_replace('/\s+/u', ' ', $string ?? '');

        $string = trim($string);

        if ($limit > 0) {
            $string = mb_substr($string, 0, $limit);

            return rtrim($string);
        }

        return $string;
    }

    /**
     * Trim both sides and convert all sequential spaces to one, but leave new lines.
     */
    public function multitrim(string $string, int $limit = 0): string
    {
        $string = preg_replace(['/\h+/u', '/\s*\v\s*/u'], [' ', "\n"], $string);

        $string = trim($string);

        if ($limit > 0) {
            $string = mb_substr($string, 0, $limit);

            return rtrim($string);
        }

        return $string;
    }

    /**
     * Cut string.
     */
    public function cut(string $string, int $min, ?int $max = null): string
    {
        $string = preg_replace('/\s+/u', ' ', $string);

        $string = trim($string);

        if (mb_strlen($string) > $min) {
            if (isset($max)) {
                if (preg_match(sprintf('/^(.{%d,%d}?)[^\p{L}\d]/u', $min, $max - 1), $string, $M)) {
                    $string = $M[1];
                } else {
                    $string = mb_substr($string, 0, $max - 1);

                    $string = rtrim($string);
                }
            } else {
                $string = mb_substr($string, 0, $min - 1);

                $string = rtrim($string);
            }

            $string .= '...';
        }

        return $string;
    }

    /**
     * Generate random string.
     */
    public function random(int $limit = 32): string
    {
        $sequence = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $min = 0;

        $max = 61;

        $string = str_repeat(' ', $limit);

        while ($limit-- > 0) {
            $string[$limit] = $sequence[mt_rand($min, $max)];
        }

        return $string;
    }
}
