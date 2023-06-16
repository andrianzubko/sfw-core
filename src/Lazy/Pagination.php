<?php

namespace SFW\Lazy;

/**
 * Paginal navigation.
 */
class Pagination extends \SFW\Lazy
{
    /**
     * Paginal navigation calculation and return as array.
     */
    public function calc(int $totalEntries, int $entriesPerPage, int $pagesPerSet, int $currentPage): array
    {
        return (array) new \SFW\Paginator(...func_get_args());
    }
}
