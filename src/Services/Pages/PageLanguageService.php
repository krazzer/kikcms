<?php

namespace KikCMS\Services\Pages;

use KikCmsCore\Services\DbService;
use KikCmsCore\Classes\Model;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\Page;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguage;
use KikCMS\Models\PageLanguageContent;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCMS\ObjectLists\PageMap;
use KikCMS\Services\LanguageService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Service for handling Page Model objects
 *
 * @property DbService $dbService
 * @property LanguageService $languageService
 * @property PageService $pageService
 */
class PageLanguageService extends Injectable
{
    /**
     * Create PageLanguages for the alias
     *
     * @param Page $alias
     */
    public function createForAlias(Page $alias)
    {
        if( ! $alias->getAliasId()){
            return;
        }
        
        $pageLanguageMap = $this->getAllByPageId($alias->getAliasId());

        foreach ($pageLanguageMap as $pageLanguage) {
            $aliasPageLanguage = new PageLanguage();

            $aliasPageLanguage->language_code = $pageLanguage->getLanguageCode();
            $aliasPageLanguage->page_id       = $alias->getId();

            $aliasPageLanguage->save();
        }
    }

    /**
     * @param Page $page
     * @param string|null $languageCode
     * @return null|PageLanguage
     */
    public function getByPage(Page $page, string $languageCode = null): ?PageLanguage
    {
        return $this->getByPageId($page->getId(), $languageCode);
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
            ->from(PageLanguage::class)
            ->where('page_id = :pageId: AND language_code = :langCode:', [
                'pageId' => $pageId, 'langCode' => $languageCode
            ]);

        return $this->dbService->getObject($query);
    }

    /**
     * @param string $pageKey
     * @param string $languageCode
     * @return null|Model|PageLanguage
     */
    public function getByPageKey(string $pageKey, string $languageCode): ?PageLanguage
    {
        $query = (new Builder)
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'pl.page_id = p.id', 'p')
            ->where('p.key = :pageKey: AND pl.language_code = :langCode:', [
                'pageKey'  => $pageKey,
                'langCode' => $languageCode
            ]);

        return $this->dbService->getObject($query);
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
     * @param PageMap $pageMap
     * @param string $languageCode
     * @param bool $activeOnly
     * @return PageLanguageMap
     */
    public function getByPageMap(PageMap $pageMap, string $languageCode = null, bool $activeOnly = true): PageLanguageMap
    {
        if( ! $pageMap->keys()){
            return new PageLanguageMap;
        }

        $languageCode = $languageCode ?: $this->languageService->getDefaultLanguageCode();

        $query = (new Builder)
            ->from(PageLanguage::class)
            ->where('page_id IN ({ids:array}) AND language_code = :langCode:', [
                'ids' => $pageMap->keys(), 'langCode' => $languageCode
            ]);

        if($activeOnly){
            $query->andWhere('active = 1');
        }

        return $this->dbService->getObjectMap($query, PageLanguageMap::class, PageLanguage::FIELD_PAGE_ID);
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
            ->leftJoin(PageContent::class, 'pc.page_id = p.id', 'pc')
            ->where('p.id IN ({ids:array})', ['ids' => $pageMap->keys()])
            ->columns(['pc.page_id AS pageId', 'pc.value', 'pc.field']);

        $queryPageLanguageContent = (new Builder)
            ->from(['p' => Page::class])
            ->leftJoin(PageLanguageContent::class, 'plc.page_id = p.id AND plc.language_code = "' . $langCode . '"', 'plc')
            ->where('p.id IN ({ids:array})', ['ids' => $pageMap->keys()])
            ->columns(['plc.page_id AS pageId', 'plc.value', 'plc.field']);

        $rows  = $this->dbService->getRows($queryPageContent) + $this->dbService->getRows($queryPageLanguageContent);
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
     * @param PageLanguage $pageLanguageAlias
     * @return PageLanguageMap
     */
    public function getPath(PageLanguage $pageLanguage, PageLanguage $pageLanguageAlias): PageLanguageMap
    {
        $lft = $pageLanguageAlias->page->lft;
        $rgt = $pageLanguageAlias->page->rgt;

        if ( ! $lft || ! $rgt) {
            $pageLanguageMap = (new PageLanguageMap())->add($pageLanguageAlias, $pageLanguageAlias->page_id);
        } else {
            $query = (new Builder)
                ->from(['pl' => PageLanguage::class])
                ->join(Page::class, 'p.id = pl.page_id', 'p')
                ->where('p.lft <= :lft: AND p.rgt >= :rgt: AND p.type != "menu" AND pl.language_code = :langCode:', [
                    'lft'      => $pageLanguageAlias->page->lft,
                    'rgt'      => $pageLanguageAlias->page->rgt,
                    'langCode' => $pageLanguage->getLanguageCode(),
                ])->orderBy('lft ASC');

            /** @var PageLanguageMap $pageLanguageMap */
            $pageLanguageMap = $this->dbService->getObjectMap($query, PageLanguageMap::class, PageLanguage::FIELD_PAGE_ID);
        }

        if ($pageLanguageAlias->getPageId() !== $pageLanguage->getPageId()) {
            $pageLanguageMap->getLast()->setAliasName($pageLanguage->getName());
        }

        return $pageLanguageMap;
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return PageLanguageMap
     */
    public function getSiblings(PageLanguage $pageLanguage): PageLanguageMap
    {
        if( ! $pageLanguage->page->parent){
            return new PageLanguageMap;
        }

        $pageMap = $this->pageService->getChildren($pageLanguage->page->parent);

        return $this->getByPageMap($pageMap, $pageLanguage->getLanguageCode());
    }
}