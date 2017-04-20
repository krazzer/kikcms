<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Models\Page;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Service for handling Page Model objects
 *
 * @property DbService $dbService
 */
class PageService extends Injectable
{
    /**
     * @param Page $page
     * @return Page[] [pageId => Page] (PageMap)
     */
    public function getChildren(Page $page): array
    {
        $pagesResult = Page::find([
            'conditions' => 'lft > :lft: AND rgt < :rgt:',
            'bind'       => ['lft' => $page->lft, 'rgt' => $page->rgt]
        ]);

        $pages = [];

        foreach ($pagesResult as $page){
            $pages[$page->id] = $page;
        }

        return $pages;
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

    /**
     * Walks upwards in the page tree until it finds a page that has maxLevel set
     *
     * @param Page $page
     * @return Page|null
     */
    public function getMaxLevelDeterminer(Page $page)
    {
        return Page::findFirst([
            'conditions' => 'lft < :lft: AND rgt > :rgt: AND menu_max_level IS NOT NULL',
            'bind'       => ['lft' => $page->lft, 'rgt' => $page->rgt],
            'order'      => Page::FIELD_LFT . ' desc',
        ]);
    }

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
}