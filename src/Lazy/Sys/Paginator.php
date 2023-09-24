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
    public function calc(int $totalEntries, int $entriesPerPage, int $pagesPerSet, int $currentPage): array
    {
        $pagination = (new \SFW\Paginator(...func_get_args()))->toArray();

        $pagination['url'] = preg_replace('/[&?]i=[^&?]*/u', '',
            $_SERVER['REQUEST_URI']
        );

        $pagination['url'] .= sprintf('%s%s=',
            str_contains($pagination['url'], '?')
                ? '&' : '?',

            self::$config['sys']['paginator']['param']
        );

        return $pagination;
    }
}
