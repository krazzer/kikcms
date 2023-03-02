<?php declare(strict_types=1);

namespace KikCMS\Services\Pages;

use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\CacheConfig;
use KikCmsCore\Classes\Model;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\Page;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguage;
use KikCMS\Models\PageLanguageContent;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCMS\ObjectLists\PageMap;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Service for handling Page Model objects
 */
class PageLanguageService extends Injectable
{
    /**
     * @param PageLanguage $pageLanguage
     */
    public function checkAndUpdateSlug(PageLanguage $pageLanguage)
    {
        // url is set, so do nothing
        if ($pageLanguage->getSlug()) {
            return;
        }

        // menu's and links don't require urls
        if (in_array($pageLanguage->page->type, [Page::TYPE_MENU, Page::TYPE_LINK])) {
            return;
        }

        $urlPath = $this->urlService->toSlug($pageLanguage->getName());

        $pageLanguage->setSlug($urlPath);

        if ($parent = $pageLanguage->getParentWithSlug()) {
            $urlPath = $this->urlService->getUrlByPageLanguage($parent) . '/' . $urlPath;
        }

        if ($this->urlService->urlPathExists($urlPath, $pageLanguage)) {
            $this->urlService->deduplicateUrl($pageLanguage);
        }
    }

    /**
     * Create PageLanguages for the alias
     *
     * @param Page $alias
     */
    public function createForAlias(Page $alias)
    {
        if ( ! $alias->getAliasId()) {
            return;
        }

        $pageLanguageMap = $this->getAllByPageId($alias->getAliasId());

        foreach ($pageLanguageMap as $pageLanguage) {
            $aliasPageLanguage = new PageLanguage();

            $aliasPageLanguage->language_code = $pageLanguage->getLanguageCode();
            $aliasPageLanguage->name          = $pageLanguage->getName();
            $aliasPageLanguage->page_id       = $alias->getId();

            $aliasPageLanguage->save();
        }
    }

    /**
     * Get the PageLanguage by given pageId, using the default languageCode
     *
     * @param int $pageId
     * @param string|null $languageCode
     * @return null|PageLanguage
     */
    public function getByPageId(int $pageId, string $languageCode = null): ?PageLanguage
    {
        $languageCode = $languageCode ?: $this->languageService->getDefaultLanguageCode();

        $query = (new Builder)
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'IFNULL(p.alias, p.id) = pl.page_id', 'p')
            ->where('p.id = :pId: AND pl.language_code = :code:', ['pId' => $pageId, 'code' => $languageCode]);

        /** @var PageLanguage $pageLanguage */
        $pageLanguage = $this->dbService->getObject($query);

        if ($page = Page::getById($pageId)) {
            if ($page->getAliasId()) {
                $pageLanguage->setAliasPage($page);
            }
        }

        return $pageLanguage;
    }

    /**
     * @param string $pageKey
     * @param string $languageCode
     * @return null|Model|PageLanguage
     */
    public function getByPageKey(string $pageKey, string $languageCode): ?PageLanguage
    {
        $cacheKey = CacheConfig::PAGE_LANGUAGE_FOR_KEY . CacheConfig::SEPARATOR . $pageKey . CacheConfig::SEPARATOR .
            $languageCode;

        return $this->cacheService->cache($cacheKey, function () use ($pageKey, $languageCode) {
            $query = (new Builder)
                ->from(['pl' => PageLanguage::class])
                ->join(Page::class, 'pl.page_id = p.id', 'p')
                ->where('p.key = :pageKey: AND pl.language_code = :langCode:', [
                    'pageKey'  => $pageKey,
                    'langCode' => $languageCode
                ]);

            return $this->dbService->getObject($query);
        }, CacheConfig::ONE_DAY, true);
    }

    /**
     * Get PageLanguages for the given pageId
     *
     * @param int $pageId
     * @return PageLanguageMap
     */
    public function getAllByPageId(int $pageId): PageLanguageMap
    {
        $query = (new Builder)
            ->from(PageLanguage::class)
            ->where('page_id = :pageId:', ['pageId' => $pageId]);

        return $this->dbService->getObjectMap($query, PageLanguageMap::class, PageLanguage::FIELD_LANGUAGE_CODE);
    }

    /**
     * @param array $pageIdList
     * @param string|null $langCode
     * @param bool $activeOnly
     * @return PageLanguageMap
     */
    public function getByIdList(array $pageIdList, string $langCode = null, bool $activeOnly = true): PageLanguageMap
    {
        if ( ! $pageIdList) {
            return new PageLanguageMap;
        }

        $langCode = $langCode ?: $this->languageService->getDefaultLanguageCode();

        $query = (new Builder)
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'pl.page_id = IFNULL(p.alias, p.id)', 'p')
            ->inWhere('p.id', $pageIdList)
            ->andWhere('pl.language_code = :c:', ['c' => $langCode]);

        if ($activeOnly) {
            $query->andWhere('pl.active = 1');
        }

        return $this->dbService->getObjectMap($query, PageLanguageMap::class, PageLanguage::FIELD_PAGE_ID);
    }

    /**
     * @param PageMap $pageMap
     * @param string|null $langCode
     * @param bool $activeOnly
     * @return PageLanguageMap
     */
    public function getByPageMap(PageMap $pageMap, string $langCode = null, bool $activeOnly = true): PageLanguageMap
    {
        return $this->getByIdList($pageMap->keys(), $langCode, $activeOnly);
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return PageLanguageMap
     */
    public function getChildren(PageLanguage $pageLanguage): PageLanguageMap
    {
        $pageMap = $this->pageService->getChildren($pageLanguage->page);

        return $this->getByPageMap($pageMap, $pageLanguage->getLanguageCode());
    }

    /**
     * Get the default pageLanguage (homepage), in the default language
     *
     * @return PageLanguage
     */
    public function getDefault()
    {
        $defaultLanguageCode = $this->config->application->defaultLanguage;
        return $this->getByPageKey(KikCMSConfig::KEY_PAGE_DEFAULT, $defaultLanguageCode);
    }

    /**
     * @param string|null $languageCode
     * @return null|PageLanguage
     */
    public function getNotFoundPage(string $languageCode = null): ?PageLanguage
    {
        $languageCode = $languageCode ?: $this->config->application->defaultLanguage;
        return $this->getByPageKey(KikCMSConfig::KEY_PAGE_NOT_FOUND, $languageCode);
    }

    /**
     * @param PageMap $pageMap
     * @param string $langCode
     * @return array
     */
    public function getPageFieldTable(PageMap $pageMap, string $langCode): array
    {
        if ($pageMap->isEmpty()) {
            return [];
        }

        $queryPageContent = (new Builder)
            ->from(['p' => Page::class])
            ->join(PageContent::class, 'pc.page_id = IFNULL(p.alias, p.id)', 'pc')
            ->inWhere('p.id', $pageMap->keys())
            ->columns(['pc.page_id AS pageId', 'pc.value AS value', 'pc.field AS field']);

        $queryPageLanguageContent = (new Builder)
            ->from(['p' => Page::class])
            ->join(PageLanguageContent::class, 'plc.page_id = IFNULL(p.alias, p.id)', 'plc')
            ->inWhere('p.id', $pageMap->keys())
            ->andWhere('plc.language_code = :c:', ['c' => $langCode])
            ->columns(['plc.page_id AS pageId', 'plc.value AS value', 'plc.field AS field']);

        $rows = array_merge(
            $this->dbService->getRows($queryPageContent),
            $this->dbService->getRows($queryPageLanguageContent)
        );

        $table = [];

        foreach ($rows as $row) {
            $pageId   = $row['pageId'];
            $value    = $row['value'];
            $variable = $row['field'];

            if ( ! array_key_exists($pageId, $table)) {
                $table[$pageId] = [];
            }

            $table[$pageId][$variable] = $value;
        }

        return $table;
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return PageLanguageMap
     */
    public function getPath(PageLanguage $pageLanguage): PageLanguageMap
    {
        $lft = $pageLanguage->getAliasPage()->lft;
        $rgt = $pageLanguage->getAliasPage()->rgt;

        if ( ! $lft || ! $rgt) {
            return (new PageLanguageMap)->add($pageLanguage, $pageLanguage->getAliasPageId());
        }

        $pageLanguageMap = $this->getPathMap($pageLanguage);

        if ( ! $pageLanguageMap->getLast()) {
            return $pageLanguageMap;
        }

        if ($pageLanguageMap->getLast()->getAliasPageId() !== $pageLanguage->getAliasPageId()) {
            $pageLanguageMap->getLast()->setAliasPage($pageLanguage->getAliasPage());
        }

        return $pageLanguageMap;
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return PageLanguageMap
     */
    public function getSiblings(PageLanguage $pageLanguage): PageLanguageMap
    {
        if ( ! $pageLanguage->page->parent) {
            return new PageLanguageMap;
        }

        $pageMap = $this->pageService->getChildren($pageLanguage->page->parent);

        return $this->getByPageMap($pageMap, $pageLanguage->getLanguageCode());
    }

    /**
     * Remove all caches for given PageLanguage
     *
     * @param PageLanguage $pageLanguage
     */
    public function removeCache(PageLanguage $pageLanguage): void
    {
        $urlPath = trim($this->urlService->getUrlByPageLanguage($pageLanguage), '/');

        $this->cacheService->clear(CacheConfig::PAGE_LANGUAGE_FOR_URL . CacheConfig::SEPARATOR . $urlPath);
        $this->cacheService->clear(CacheConfig::getUrlKeyByPageLang($pageLanguage));
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return PageLanguageMap
     */
    private function getPathMap(PageLanguage $pageLanguage): PageLanguageMap
    {
        $query = (new Builder)
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'IFNULL(p.alias, p.id) = pl.page_id', 'p')
            ->where('p.lft <= :lft: AND p.rgt >= :rgt: AND p.type != "menu" AND pl.language_code = :langCode:', [
                'lft'      => $pageLanguage->getAliasPage()->lft,
                'rgt'      => $pageLanguage->getAliasPage()->rgt,
                'langCode' => $pageLanguage->getLanguageCode(),
            ])->orderBy('lft ASC');

        return $this->dbService->getObjectMap($query, PageLanguageMap::class, PageLanguage::FIELD_PAGE_ID);
    }
}