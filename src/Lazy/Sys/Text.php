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
        if (!isset($string)) {
            return '';
        }

        return mb_strtolower($string);
    }

    /**
     * First char to lower case.
     */
    public function lcfirst(?string $string): string
    {
        if (!isset($string)) {
            return '';
        }

        return mb_strtolower(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    /**
     * To upper case.
     */
    public function uc(?string $string): string
    {
        if (!isset($string)) {
            return '';
        }

        return mb_strtoupper($string);
    }

    /**
     * First char to upper case.
     */
    public function ucfirst(?string $string): string
    {
        if (!isset($string)) {
            return '';
        }

        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    /**
     * Trim both sides.
     */
    public function trim(?string $string): string
    {
        if (!isset($string)) {
            return '';
        }

        return trim($string, $this->spaces);
    }

    /**
     * Trim right side.
     */
    public function rtrim(?string $string): string
    {
        if (!isset($string)) {
            return '';
        }

        return rtrim($string, $this->spaces);
    }

    /**
     * Trim left side.
     */
    public function ltrim(?string $string): string
    {
        if (!isset($string)) {
            return '';
        }

        return ltrim($string, $this->spaces);
    }

    /**
     * Trim both sides and convert all sequential spaces to one.
     */
    public function fulltrim(?string $string, int $limit = 0): string
    {
        if (!isset($string)) {
            return '';
        }

        $string = trim(
            preg_replace('/\s+/u', ' ', $string)
        );

        if ($limit > 0) {
            return rtrim(
                mb_substr($string, 0, $limit)
            );
        }

        return $string;
    }

    /**
     * Trim both sides and convert all sequential spaces to one, but leave new lines.
     */
    public function multitrim(?string $string, int $limit = 0): string
    {
        if (!isset($string)) {
            return '';
        }

        $string = trim(
            preg_replace(['/\h+/u', '/\s*\v\s*/u'], [' ', "\n"], $string)
        );

        if ($limit > 0) {
            return rtrim(
                mb_substr($string, 0, $limit)
            );
        }

        return $string;
    }

    /**
     * Cut string.
     */
    public function cut(?string $string, int $min, ?int $max = null): string
    {
        if (!isset($string)) {
            return '';
        }

        $string = trim(
            preg_replace('/\s+/u', ' ', $string)
        );

        if (mb_strlen($string) > $min) {
            if (isset($max)) {
                if (preg_match(sprintf('/^(.{%d,%d}?)[^\p{L}\d]/u', $min, $max - 1), $string, $M)) {
                    $string = $M[1];
                } else {
                    $string = rtrim(
                        mb_substr($string, 0, $max - 1)
                    );
                }
            } else {
                $string = rtrim(
                    mb_substr($string, 0, $min - 1)
                );
            }

            $string .= '...';
        }

        return $string;
    }

    /**
     * Generate random string.
     */
    public function random(int $size = 32, string $chars = '[ALPHA][NUMERIC]'): string
    {
        $chars = str_replace(
            [
                '[ALPHA]',
                '[UPPER]',
                '[LOWER]',
                '[NUMERIC]',
            ], [
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz',
                '0123456789',
            ], $chars
        );

        $string = str_repeat(' ', $size);

        for ($i = 0, $max = strlen($chars) - 1; $i < $size; $i++) {
            $string[$i] = $chars[mt_rand(0, $max)];
        }

        return $string;
    }
}
