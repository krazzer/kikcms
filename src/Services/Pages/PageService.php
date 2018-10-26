<?php

namespace KikCMS\Services\Pages;

use KikCmsCore\Services\DbService;
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
     * @param array $pageIds
     * @return PageMap
     */
    public function getByIdList(array $pageIds): PageMap
    {
        $query = (new Builder)
            ->from(Page::class)
            ->inWhere(Page::FIELD_ID, $pageIds);

        return $this->dbService->getObjectMap($query, PageMap::class);
    }

    /**
     * @param string $key
     * @return Page|null
     */
    public function getByKey(string $key): ?Page
    {
        $query = (new Builder)
            ->from(Page::class)
            ->where(Page::FIELD_KEY . ' = :key:', ['key' => $key]);

        return $this->dbService->getObject($query);
    }

    /**
     * @param Page $page
     * @return PageMap
     */
    public function getChildren(Page $page): PageMap
    {
        $query = (new Builder)
            ->from(Page::class)
            ->where(Page::FIELD_PARENT_ID . ' = :parentId:', ['parentId' => $page->id])
            ->orderBy(Page::FIELD_DISPLAY_ORDER);

        return $this->dbService->getObjectMap($query, PageMap::class);
    }

    /**
     * @param Page $page
     * @return PageMap
     */
    public function getOffspring(Page $page): PageMap
    {
        return $this->dbService->getObjectMap($this->getOffspringQuery($page), PageMap::class);
    }

    /**
     * @param Page $page
     * @return Builder
     */
    public function getOffspringQuery(Page $page): Builder
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
     * @param array $pageIds
     * @return array [parentId => [offspringIds]]
     */
    public function getOffspringIdMap(array $pageIds): array
    {
        $query = (new Builder)
            ->columns(['p.id', 'GROUP_CONCAT(cp.id)'])
            ->from(['p' => Page::class])
            ->leftJoin(Page::class, 'p.lft < cp.lft AND p.rgt > cp.rgt', 'cp')
            ->inWhere('p.id', $pageIds)
            ->groupBy('p.id');

        $offspringIdMap = $this->dbService->getAssoc($query);

        foreach ($offspringIdMap as $id => $offspringIds){
            $offspringIdMap[$id] = explode(',', $offspringIds);
        }

        return $offspringIdMap;
    }

    /**
     * @param Page $page
     * @return int
     */
    public function getHighestDisplayOrderChild(Page $page): int
    {
        $query = (new Builder)
            ->from(Page::class)
            ->columns('MAX(' . Page::FIELD_DISPLAY_ORDER . ')')
            ->where('parent_id = :parentId:', ['parentId' => $page->id]);

        return (int) $this->dbService->getValue($query);
    }

    /**
     * Walks upwards in the page tree until it finds a page that has maxLevel set
     *
     * @param Page $page
     * @return null|Page
     */
    public function getMaxLevelDeterminer(Page $page): ?Page
    {
        return Page::findFirst([
            'conditions' => 'lft < :lft: AND rgt > :rgt: AND menu_max_level IS NOT NULL',
            'bind'       => ['lft' => $page->lft, 'rgt' => $page->rgt],
            'order'      => Page::FIELD_LFT . ' desc',
        ]);
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
    public function getSelect($parentId = 0, PageMap $pageMap, PageLanguageMap $pageLangMap = null, $level = 0): array
    {
        if ( ! $pageLangMap) {
            $pageLangMap = $this->pageLanguageService->getByPageMap($pageMap, null, false);
        }

        $selectArray = [];

        foreach ($pageMap as $pageId => $page) {
            if ($page->parent_id != $parentId) {
                continue;
            }

            $prefix = str_repeat('&nbsp;', $level * 5) . ($level % 2 ? 'ο' : '•') . ' ';

            $selectArray[$pageId] = $prefix . $pageLangMap->get($pageId)->getName();

            $subArray    = $this->getSelect($pageId, clone $pageMap, $pageLangMap, $level + 1);
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
    public function isOffspringOf(Page $page, Page $parentPage): bool
    {
        $query = (new Builder)
            ->from(Page::class)
            ->columns(Page::FIELD_ID)
            ->where('lft > :lft: AND rgt < :rgt:', [
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
    public function getOffspringByMenu(Menu $menu): PageMap
    {
        if ( ! $menuPage = Page::getById($menu->getMenuId())) {
            return new PageMap();
        }

        $query = $this->getOffspringQuery($menuPage);

        if ($menu->getRestrictTemplate()) {
            $query->andWhere(Page::FIELD_TEMPLATE . ' = :templateId:', ['templateId' => $menu->getRestrictTemplate()]);
        }

        if ($menu->getMaxLevel()) {
            $query->andWhere('level <= ' . (int) ($menuPage->level + $menu->getMaxLevel()));
        }

        return $this->dbService->getObjectMap($query, PageMap::class);
    }

    /**
     * Retrieve all pages that have children which have no display_order set
     *
     * @return PageMap
     */
    public function getDisplayOrderMissing(): PageMap
    {
        $query = (new Builder)
            ->from(['p' => Page::class])
            ->join(Page::class, 'cp.parent_id = p.id AND cp.display_order IS NULL', 'cp')
            ->groupBy('p.id');

        return $this->dbService->getObjectMap($query, PageMap::class);
    }
}