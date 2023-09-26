<?php

namespace SFW\Lazy\Sys;

/**
 * Paginator.
 */
class Paginator extends \SFW\Lazy\Sys
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
     * Overlaying paginator class.
     */
    public function calc(
        int $totalEntries,
        int $entriesPerPage,
        int $pagesPerSet,
        int $currentPage
    ): array {
        return (new \SFW\Paginator(...func_get_args()))->toArray();
    }
}
