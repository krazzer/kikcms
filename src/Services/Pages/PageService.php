<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Classes\Frontend\Menu;
use KikCMS\Models\Page;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCMS\ObjectLists\PageMap;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

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
     * @return PageMap
     */
    public function getChildren(Page $page): PageMap
    {
        return $this->getPageMapByQuery($this->getChildrenQuery($page));
    }

    /**
     * @param Page $page
     * @return Builder
     */
    public function getChildrenQuery(Page $page): Builder
    {
        $query = (new Builder())
            ->from(Page::class)
            ->where('lft > :lft: AND rgt < :rgt:', [
                'lft' => $page->lft,
                'rgt' => $page->rgt
            ])
            ->orderBy('lft');

        return $query;
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
     * @param Builder $query
     * @return PageMap
     */
    public function getPageMapByQuery(Builder $query)
    {
        $results = $query->getQuery()->execute();
        $pageMap = new PageMap();

        /** @var Page $page */
        foreach ($results as $page) {
            $pageMap->add($page, $page->getId());
        }

        return $pageMap;
    }

    /**
     * Create an array that can be used for a select using the given Page[]
     *
     * @param int $parentId
     * @param PageMap $pageMap
     * @param PageLanguageMap $pageLangMap
     * @param int $level
     * @return array
     */
    public function getSelect($parentId = 0, PageMap $pageMap, PageLanguageMap $pageLangMap, $level = 0): array
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

            $selectArray[$pageId] = $prefix . $pageLangMap->get($pageId)->getName();

            $subArray    = $this->getSelect($pageId, $pageMap, $pageLangMap, $level + 1);
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

    /**
     * @param Menu $menu
     * @return PageMap
     */
    public function getChildrenByMenu(Menu $menu): PageMap
    {
        if ( ! $menuPage = Page::getById($menu->getMenuId())) {
            return new PageMap();
        }

        $query = $this->getChildrenQuery($menuPage);

        if ($menu->getRestrictTemplateId()) {
            $query->andWhere(Page::FIELD_TEMPLATE_ID . ' = :templateId:', ['templateId' => $menu->getRestrictTemplateId()]);
        }

        if ($menu->getMaxLevel()) {
            $query->andWhere('level <= ' . (int) ($menuPage->level + $menu->getMaxLevel()));
        }

        return $this->getPageMapByQuery($query);
    }
}