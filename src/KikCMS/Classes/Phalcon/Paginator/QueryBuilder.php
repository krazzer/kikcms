<?php

namespace KikCMS\Classes\Phalcon\Paginator;


use Phalcon\Paginator\Adapter\QueryBuilder as PhalconQueryBuilder;

class QueryBuilder extends PhalconQueryBuilder
{
    /**
     * Adds an array with the pages to be shown. null represents not clickable space (...)
     *
     * @inheritdoc
     */
    public function getPaginate()
    {
        $page  = parent::getPaginate();
        $pages = [];

        if ($page->last < 7) {
            for ($i = 1; $i <= $page->last; $i++) {
                $pages[$i] = $i;
            }
        } else {
            if ($page->current < 5) {
                $secondLast = $page->last - 1 == 6 ? 6 : null;
                $pages      = [1, 2, 3, 4, 5, $secondLast, $page->last];
            } elseif ($page->current > $page->last - 4) {
                $second = $page->last - 5 == 2 ? 2 : null;
                $pages  = [1, $second, $page->last - 4, $page->last - 3, $page->last - 2, $page->last - 1, $page->last];
            } else {
                $secondLast = $page->current + 2 == 6 ? 6 : null;
                $second     = $page->current - 2 == 2 ? 2 : null;
                $pages      = [1, $second, $page->current - 1, $page->current, $page->current + 1, $secondLast, $page->last];
            }
        }

        $page->pages = $pages;

        return $page;
    }
}