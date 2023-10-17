<?php

namespace SFW\Lazy\Sys;

/**
 * Text functions.
 */
class Text extends \SFW\Lazy\Sys
{
    /**
     * Just a placeholder.
     *
     * If your overrides constructor, don't forget call parent at first line! Even if it's empty!
     */
    public function __construct()
    {
    }

    /**
     * To lower case.
     */
    public function lc(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        return mb_strtolower($string);
    }

    /**
     * First char to lower case.
     */
    public function lcFirst(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        return mb_strtolower(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    /**
     * To upper case.
     */
    public function uc(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        return mb_strtoupper($string);
    }

    /**
     * First char to upper case.
     */
    public function ucFirst(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    /**
     * Trim both sides.
     */
    public function trim(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        return trim($string, " \t\n\r\0\x0B\x0C\u{A0}\u{FEFF}");
    }

    /**
     * Trim right side.
     */
    public function rTrim(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        return rtrim($string, " \t\n\r\0\x0B\x0C\u{A0}\u{FEFF}");
    }

    /**
     * Trim left side.
     */
    public function lTrim(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        return ltrim($string, " \t\n\r\0\x0B\x0C\u{A0}\u{FEFF}");
    }

    /**
     * Trim both sides and convert all sequential spaces to one.
     */
    public function fTrim(?string $string, int $limit = 0): string
    {
        if ($string === null) {
            return '';
        }

        $string = trim(preg_replace("/(?: |\t|\n|\r|\0|\x0B|\x0C|\u{A0}|\u{FEFF})+/S", ' ', $string));

        if ($limit <= 0) {
            return $string;
        }

        return rtrim(mb_substr($string, 0, $limit));
    }

    /**
     * Trim both sides and convert all sequential spaces to one, but leave new lines.
     */
    public function mTrim(?string $string, int $limit = 0): string
    {
        if ($string === null) {
            return '';
        }

        $string = trim(preg_replace(['/\h+/u', '/\s*\v\s*/u'], [' ', "\n"], $string));

        if ($limit <= 0) {
            return $string;
        }

        return rtrim(mb_substr($string, 0, $limit));
    }

    /**
     * Cut string.
     */
    public function cut(?string $string, int $min, ?int $max = null): string
    {
        if ($string === null) {
            return '';
        }

        $string = trim(preg_replace("/(?: |\t|\n|\r|\0|\x0B|\x0C|\u{A0}|\u{FEFF})+/S", ' ', $string));

        if (mb_strlen($string) > $min) {
            if ($max !== null) {
                if (preg_match(sprintf('/^(.{%d,%d}?)[^\p{L}\d]/u', $min, $max - 1), $string, $M)) {
                    $string = $M[1];
                } else {
                    $string = rtrim(mb_substr($string, 0, $max - 1));
                }
            } else {
                $string = rtrim(mb_substr($string, 0, $min - 1));
            }

            $string .= '...';
        }

        return $string;
    }

    /**
     * Generate random string.
     */
    public function random(int $size = 32, string $chars = '[alpha][digit]'): string
    {
        $chars = str_replace(
            [
                '[alpha]',
                '[upper]',
                '[lower]',
                '[digit]',
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
