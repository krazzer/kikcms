<?php

namespace KikCMS\Services\Model;

use KikCMS\Classes\DbService;
use KikCMS\Models\Page;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Service for handling Page Model objects
 *
 * @property DbService dbService
 */
class PageService extends Injectable
{
    /**
     * @param Page $page
     * @param Page $parentPage
     *
     * @return bool
     */
    public function isChildOf(Page $page, Page $parentPage): bool
    {
        $query = new Builder();
        $query->from(Page::class);
        $query->columns(Page::FIELD_ID);
        $query->where('lft > :lft: AND rgt < :rgt:', [
            'lft' => $parentPage->lft,
            'rgt' => $parentPage->rgt,
        ]);

        $childIds = $this->dbService->getValues($query);

        return in_array($page->id, $childIds);
    }

    /**
     * @param Page $page
     * @return int
     */
    public function getHighestDisplayOrderChild(Page $page): int
    {
        $query = new Builder();
        $query->from(Page::class);
        $query->columns('MAX(' . Page::FIELD_DISPLAY_ORDER . ')');
        $query->where('parent_id = :parentId:', ['parentId' => $page->id]);

        return (int) $this->dbService->getValue($query);
    }
}