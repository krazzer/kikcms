<?php


namespace KikCMS\Services\Util;


class PaginateListService
{
    /**
     * Create a list of pages based on the current and the last page, having a max of 7 items in the list
     * e.g.
     *
     * @param int $lastPage
     * @param int $currentPage
     * @return array
     */
    public function getPageList(int $lastPage, int $currentPage): array
    {
        $pages = [];

        if ($lastPage < 7) {
            for ($i = 1; $i <= $lastPage; $i++) {
                $pages[] = $i;
            }
        } else {
            if ($currentPage < 5) {
                $secondLast = $lastPage - 1 == 6 ? 6 : null;
                $pages      = [1, 2, 3, 4, 5, $secondLast, $lastPage];
            } elseif ($currentPage > $lastPage - 4) {
                $second = $lastPage - 5 == 2 ? 2 : null;
                $pages  = [1, $second, $lastPage - 4, $lastPage - 3, $lastPage - 2, $lastPage - 1, $lastPage];
            } else {
                $secondLast = $currentPage + 2 == 6 ? 6 : null;
                $second     = $currentPage - 2 == 2 ? 2 : null;
                $pages      = [1, $second, $currentPage - 1, $currentPage, $currentPage + 1, $secondLast, $lastPage];
            }
        }

        return $pages;
    }
}