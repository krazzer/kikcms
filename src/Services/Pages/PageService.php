<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Models\Page;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Resultset;

/**
 * Service for handling Page Model objects
 *
 * @property DbService $dbService
 * @property PageLanguageService $pageLanguageService
 */
class PageService extends Injectable
{
    /**
     * @param Page $page
     * @return Page[] [pageId => Page] (PageMap)
     */
    public function getChildren(Page $page): array
    {
        $pagesResult = $this->getChildrenQuery($page)->getQuery()->execute();

        return $this->getPageMap($pagesResult);
    }

    /**
     * @param Page $page
     * @return Builder
     */
    public function getChildrenQuery(Page $page): Builder
    {
        return (new Builder())
            ->from(Page::class)
            ->where('lft > :lft: AND rgt < :rgt:', [
                'lft' => $page->lft,
                'rgt' => $page->rgt
            ])
            ->orderBy('lft');
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
     * @param Resultset $resultset
     * @return Page[] [pageId => Page] (PageMap)
     */
    public function getPageMap(Resultset $resultset)
    {
        $pages = [];

        foreach ($resultset as $page){
            $pages[$page->id] = $page;
        }

        return $pages;
    }

    /**
     * Create an array that can be used for a select using the given Page[]
     *
     * @param array $pageMap [pageId => Page object]
     * @param array $pageLangMap
     * @param int $parentId
     * @param int $level
     *
     * @return array
     */
    public function getCategorySelect($parentId = 0, array $pageMap = [], array $pageLangMap = [], $level = 0): array
    {
        if ( ! $pageLangMap) {
            $pageLangMap = $this->pageLanguageService->getByPageMap($pageMap);
        }

        $selectArray = [];

        foreach ($pageMap as $pageId => $page) {
            if ($page->parent_id != $parentId) {
                continue;
            }

            $prefix = str_repeat('&nbsp;', $level * 5) . ($level % 2 ? 'ο' : '•') . ' ';

            $selectArray[$pageId] = $prefix . $pageLangMap[$pageId]->name;

            $subArray    = $this->getCategorySelect($pageId, $pageMap, $pageLangMap, $level + 1);
            $selectArray = $selectArray + $subArray;
        }

        return $selectArray;
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