<?php

namespace SFW\Lazy\Sys;

/**
 * Number functions.
 */
class Number extends \SFW\Lazy\Sys
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
     * Gets number from anything.
     */
    public function cast(mixed $number, int $precision = 0, ?float $min = null, ?float $max = null): float
    {
        if (isset($number) && is_scalar($number)) {
            if (is_string($number)) {
                $number = strtr($number, ',', '.');
            }

            $number = round((float) $number, $precision);
        } else {
            $number = 0;
        }

        if (isset($min) && $number < $min) {
            $number = $min;
        }

        if (isset($max) && $number > $max) {
            $number = $max;
        }

        return $number;
    }
}
