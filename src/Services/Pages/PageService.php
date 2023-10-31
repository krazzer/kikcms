<?php declare(strict_types=1);

namespace KikCMS\Services\Pages;

use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Config\CacheConfig;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Frontend\Menu;
use KikCMS\Models\Page;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCMS\ObjectLists\PageMap;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Service for handling Page Model objects
 *
 * @property DbService $dbService
 * @property PageLanguageService $pageLanguageService
 * @property WebsiteSettingsBase $websiteSettings
 */
class PageService extends Injectable
{
    /**
     * @param Page $page
     * @return bool
     */
    public function checkForDisplayOrderCollision(Page $page): bool
    {
        $query = (new Builder)
            ->from($this->websiteSettings->getPageClass())
            ->where(Page::FIELD_ID . ' != ' . $page->getId())
            ->andWhere(Page::FIELD_PARENT_ID . ' = ' . $page->getParentId())
            ->andWhere(Page::FIELD_DISPLAY_ORDER . ' = ' . $page->getDisplayOrder());

        return $this->dbService->getExists($query);
    }

    /**
     * @param array $pageIds
     * @return PageMap
     */
    public function getByIdList(array $pageIds): PageMap
    {
        $query = (new Builder)
            ->from($this->websiteSettings->getPageClass())
            ->inWhere(Page::FIELD_ID, $pageIds);

        return $this->dbService->getObjectMap($query, PageMap::class);
    }

    /**
     * @param string $key
     * @return Page|null
     */
    public function getByKey(string $key): ?Page
    {
        $cacheKey = CacheConfig::PAGE_FOR_KEY . CacheConfig::SEPARATOR . $key;

        return $this->cacheService->cache($cacheKey, function () use ($key) {
            $query = (new Builder)
                ->from($this->websiteSettings->getPageClass())
                ->where(Page::FIELD_KEY . ' = :key:', ['key' => $key]);

            return $this->dbService->getObject($query);
        }, CacheConfig::ONE_DAY, true);
    }

    /**
     * @param Page $page
     * @param int|null $limit
     * @return PageMap
     */
    public function getChildren(Page $page, int $limit = null): PageMap
    {
        $query = (new Builder)
            ->from($this->websiteSettings->getPageClass())
            ->where(Page::FIELD_PARENT_ID . ' = :parentId:', ['parentId' => $page->id])
            ->orderBy(Page::FIELD_DISPLAY_ORDER);

        if($limit){
            $query->limit($limit);
        }

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
        return (new Builder)
            ->from(['p' => $this->websiteSettings->getPageClass()])
            ->where('lft > :lft: AND rgt < :rgt:', [
                'lft' => $page->lft,
                'rgt' => $page->rgt
            ])
            ->orderBy('lft');
    }

    /**
     * @param array $pageIds
     * @return array [parentId => [offspringIds]]
     */
    public function getOffspringIdMap(array $pageIds): array
    {
        $query = (new Builder)
            ->columns(['p.id', 'GROUP_CONCAT(cp.id)'])
            ->from(['p' => $this->websiteSettings->getPageClass()])
            ->leftJoin(Page::class, 'p.lft < cp.lft AND p.rgt > cp.rgt', 'cp')
            ->inWhere('p.id', $pageIds)
            ->andWhere('cp.id IS NOT NULL')
            ->groupBy('p.id');

        $offspringIdMap = $this->dbService->getAssoc($query);

        foreach ($offspringIdMap as $id => $offspringIds) {
            $offspringIdMap[$id] = explode(',', $offspringIds);
        }

        return $offspringIdMap;
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
    public function getSelect($parentId, PageMap $pageMap, PageLanguageMap $pageLangMap = null, $level = 0): array
    {
        if ( ! $pageLangMap) {
            $pageLangMap = $this->pageLanguageService->getByPageMap($pageMap, null, false);
        }

        $selectArray = [];

        foreach ($pageMap as $pageId => $page) {
            if ($page->parent_id != $parentId) {
                continue;
            }

            $prefix = str_repeat('&nbsp;', $level * 10) . ($level % 2 ? 'ο' : '•') . ' ';

            if($pageLang = $pageLangMap->get($pageId)) {
                $selectArray[$pageId] = $prefix . $pageLang->getName();
            }

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
        $query = $this->getOffspringQuery($parentPage)->columns(Page::FIELD_ID);

        $childIds = $this->dbService->getValues($query);

        return in_array($page->id, $childIds);
    }

    /**
     * @param Menu $menu
     * @return PageMap
     */
    public function getOffspringByMenu(Menu $menu): PageMap
    {
        $pageId = $this->pageService->getIdByKeyOrId($menu->getMenuKey());

        if ( ! $menuPage = Page::getById($pageId)) {
            return new PageMap();
        }

        $query = $this->getOffspringQueryByMenu($menu, $menuPage);

        return $this->dbService->getObjectMap($query, PageMap::class);
    }

    /**
     * @param Page $page
     * @return PageMap
     */
    public function getOffspringAliases(Page $page): PageMap
    {
        $query = $this->getOffspringQuery($page)
            ->inWhere(Page::FIELD_TYPE, [Page::TYPE_ALIAS]);

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
            ->from(['p' => $this->websiteSettings->getPageClass()])
            ->join(Page::class, 'cp.parent_id = p.id AND cp.display_order IS NULL', 'cp')
            ->groupBy('p.id');

        return $this->dbService->getObjectMap($query, PageMap::class);
    }

    /**
     * @param mixed $menuKeyOrId
     * @return int|null
     */
    public function getIdByKeyOrId($menuKeyOrId): ?int
    {
        if (is_numeric($menuKeyOrId)) {
            return (int) $menuKeyOrId;
        }

        if( ! $page = $this->getByKey($menuKeyOrId)) {
            return null;
        }

        return $page->getId();
    }

    /**
     * Get an array with all pages with a key and the template they have
     *
     * @return array [key => template]
     */
    public function getKeyTemplateMap(): array
    {
        $query = (new Builder)
            ->from(Page::class)
            ->columns([Page::FIELD_KEY, Page::FIELD_TEMPLATE])
            ->where(Page::FIELD_KEY . ' IS NOT NULL');

        return $this->dbService->getAssoc($query);
    }

    /**
     * Checks if a Page needs the required nested set properties set
     *
     * @param Page $page
     * @return bool
     */
    public function requiresNesting(Page $page): bool
    {
        // no parent, doesn't need nesting
        if ( ! $page->getParentId()) {
            return false;
        }

        // lft and rgt are already set, so no need to nest
        if (isset($page->lft) && $page->lft && isset($page->rgt) && $page->rgt) {
            return false;
        }

        return true;
    }

    /**
     * @param string $template
     * @return PageMap
     */
    public function getByTemplate(string $template): PageMap
    {
        $query = (new Builder)
            ->from($this->websiteSettings->getPageClass())
            ->where(Page::FIELD_TEMPLATE . ' = :template:', ['template' => $template])
            ->orderBy(Page::FIELD_DISPLAY_ORDER);

        return $this->dbService->getObjectMap($query, PageMap::class);
    }

    /**
     * @param Menu $menu
     * @param Page $menuPage
     * @return Builder
     */
    protected function getOffspringQueryByMenu(Menu $menu, Page $menuPage): Builder
    {
        $query = $this->getOffspringQuery($menuPage);

        if ($menu->getRestrictTemplates()) {
            $query->inWhere('(SELECT a.template FROM ' . Page::class. ' a WHERE a.id = IFNULL(p.alias, p.id))', $menu->getRestrictTemplates());
        }

        if ($menu->getMaxLevel()) {
            $query->andWhere('level <= ' . (int) ($menuPage->level + $menu->getMaxLevel()));
        }

        return $query;
    }
}