<?php

namespace SFW\Lazy;

/**
 * Number functions.
 */
class Number extends \SFW\Lazy
{
    /**
     * Getting number from anything.
     */
    public function cast(mixed $number, int $precision = 0, ?float $min = null, ?float $max = null): float
    {
        if (!isset($number) || !is_scalar($number) || !mb_check_encoding($number)) {
            return round($min, $precision);
        }

        $number = (float) preg_replace(['/\s+/u', '/[\.\,]+/u'], ['', '.'], $number);

        $number = round($number, $precision);

        if (isset($min)) {
            $number = max($min, $number);
        }

        if (isset($max)) {
            $number = min($max, $number);
        }

        return $number;
    }

    /**
     * Generating string with numbers.
     */
    public function random(int $limit = 16): string
    {
        $sequence = '0123456789';

        $min = 0;

        $max = 9;

        $number = str_repeat(' ', $limit);

        while ($limit-- > 0) {
            $number[$limit] = $sequence[mt_rand($min, $max)];
        }

        return $number;
    }
}
