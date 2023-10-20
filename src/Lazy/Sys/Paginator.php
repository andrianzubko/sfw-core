<?php

namespace SFW\Lazy\Sys;

/**
 * Paginator.
 */
class Paginator extends \SFW\Lazy\Sys
{
    /**
     * Prepared url for page number substitution.
     */
    protected string $url;

    /**
     * Building url for page number substitution.
     *
     * If your overrides constructor, don't forget call parent at first line!
     */
    public function __construct()
    {
        $param = self::$config['sys']['paginator']['param'];

        if ($param !== null) {
            parse_str($_SERVER['QUERY_STRING'], $query);

            unset($query[$param]);

            $query[$param] = '';

            $this->url = $_SERVER['REQUEST_PATH'] . '?' . http_build_query($query);
        }
    }

    /**
     * Calculates page-by-page navigation.
     */
    public function calc(int $totalEntries, int $entriesPerPage, int $pagesPerSet, int $currentPage): array
    {
        $pagination = (new \SFW\Paginator(...func_get_args()))->toArray();

        if (isset($this->url)) {
            $pagination['url'] = $this->url;
        }

        return $pagination;
    }
}
