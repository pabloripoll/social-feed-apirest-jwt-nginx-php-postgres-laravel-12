<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;

class Paginate
{
    /**
     * Paginate listing information to perform db select query
     *
     * @param  $total    collection total result without filters
     * @param  $filters  filters applied to db query without page and result limit
     */
    public static function listing(?int $total = 1, ?array $filters = null): object
    {
        // defaults
        $page  = 1;
        $limit = 30;

        // result rows limit and cast - allow numeric strings
        if (request()->has('limit')) {
            $reqLimit = request()->input('limit');
            $reqLimit = is_numeric($reqLimit) ? (int) $reqLimit : $reqLimit;
            $reqLimit = is_int($reqLimit) && $reqLimit < 1 ? 1 : $reqLimit;
            $limit = $reqLimit <= 100 ? $reqLimit : $limit;
        }

        // results devided into pages by query limit
        $pages = (int) ceil($total / $limit);

        // page cast - allow numeric strings
        if (request()->has('page')) {
            $reqPage = request()->input('page');
            $reqPage = is_numeric($reqPage) ? (int) $reqPage : $reqPage;
            $reqPage = is_int($reqPage) && $reqPage < 1 ? 1 : $reqPage;
            $page = $reqPage <= $pages ? $page : $pages;
        }

        // pagination links
        $base = '/'.request()->route()->uri();
        $prevPage  = $page == 1 ? null : $base.'?page=' . ($page - 1) . '&limit=' . ($limit) . (! $filters ? '' : http_build_query($filters));
        $nextPage  = $page >= $pages ? null : $base.'?page=' . ($page + 1) . '&limit=' . ($limit) . (! $filters ? '' : http_build_query($filters));
        $firstPage = $base.'?page=1&limit=' . ($limit) . (! $filters ? '' : http_build_query($filters));
        $lastPage  = $base.'?page=' . $pages . '&limit=' . ($limit) . (! $filters ? '' : http_build_query($filters));

        // output
        $params = new \stdClass;
        $params->page  = $page;
        $params->limit = $limit;
        $params->total = $total;
        $params->pages = $pages;
        $params->prev_page  = $prevPage;
        $params->next_page  = $nextPage;
        $params->first_page = $firstPage;
        $params->last_page  = $lastPage;

        return $params;
    }

    /**
     * Pagination applied to a given collection
     */
    public static function collection($collection): LengthAwarePaginator
    {
        $itemsPerPage = 15;

        $page = request()->get('page', 10);

        $queryParams = request()->query();

        unset($queryParams['q']); // remove unwanted query params

        return new LengthAwarePaginator(
            $collection->forPage($page, $itemsPerPage),
            $collection->count(),
            $itemsPerPage,
            $page,
            [
                'path' => request()->root().'/'.request()->path(),
                'query' => $queryParams,
            ]
        );
    }
}
