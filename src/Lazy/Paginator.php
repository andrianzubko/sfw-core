<?php

namespace SFW\Lazy;

/**
 * Paginal navigation.
 */
class Paginator extends \SFW\Lazy
{
    /**
     * Just in case.
     */
    public function __construct() {}

    /**
     * Paginal navigation calculation and return as array.
     */
    public function calc(int $totalEntries, int $entriesPerPage, int $pagesPerSet, int $currentPage): array
    {
        return (array) new \SFW\Paginator(...func_get_args());
    }
}
