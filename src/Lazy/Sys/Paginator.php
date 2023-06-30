<?php

namespace SFW\Lazy\Sys;

/**
 * Paginator.
 */
class Paginator extends \SFW\Lazy\Sys
{
    /**
     * Param can be changed at nested class.
     */
    protected string $param = 'i';

    /**
     * Url can be changed at nested class.
     */
    protected ?string $url = null;

    /**
     * Overlaying paginator class.
     */
    public function calc(int $totalEntries, int $entriesPerPage, int $pagesPerSet, int $currentPage): \SFW\Paginator
    {
        return new \SFW\Paginator(
            $totalEntries,
            $entriesPerPage,
            $pagesPerSet,
            $currentPage,
            $this->param,
            $this->url
        );
    }
}
