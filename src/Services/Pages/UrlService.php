<?php

namespace KikCMS\Services\Pages;


use KikCMS\Classes\Translator;
use KikCmsCore\Services\DbService;
use KikCMS\Config\CacheConfig;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\CacheService;
use KikCMS\Services\LanguageService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 * @property CacheService $cacheService
 * @property PageService $pageService
 * @property PageLanguageService $pageLanguageService
 * @property LanguageService $languageService
 * @property Translator $translator
 */
class UrlService extends Injectable
{
    /**
     * Create urls for the given pageId in all languages if they are not present yet
     *
     * @param int $pageId
     */
    public function createUrlsForPageId(int $pageId)
    {
        $pageLanguageMap = $this->pageLanguageService->getAllByPageId($pageId);

        foreach ($pageLanguageMap as $pageLanguage) {
            $pageLanguage->url = $this->toSlug($this->getName($pageLanguage));

            if ($this->urlExistsForPageLanguage($pageLanguage)) {
                $this->deduplicateUrl($pageLanguage);
            } else {
                $pageLanguage->save();
            }
        }
    }

    /**
     * @param PageLanguage $pageLanguage
     */
    public function deduplicateUrl(PageLanguage $pageLanguage)
    {
        $newUrlIndex = 1;

        $newUrl = $pageLanguage->url . '-' . $newUrlIndex;

        while ($this->urlExists($newUrl, $pageLanguage->page->parent_id, $pageLanguage->language_code, $pageLanguage)) {
            $newUrlIndex++;
            $newUrl = $pageLanguage->url . '-' . $newUrlIndex;
        }

        $pageLanguage->url = $newUrl;
        $pageLanguage->save();

        $this->cacheService->clearPageCache();
    }

    /**
     * @param string $urlPath
     * @return null|PageLanguage
     */
    public function getPageLanguageByUrlPath(string $urlPath): ?PageLanguage
    {
        // remove leading slash
        if (substr($urlPath, 0, 1) == '/') {
            $urlPath = substr($urlPath, 1);
        }

        $cacheKey = CacheConfig::PAGE_LANGUAGE_FOR_URL . ':' . $urlPath;

        return $this->cacheService->cache($cacheKey, function () use ($urlPath) {
            $urlMap = $this->getPossibleUrlMapByUrl($urlPath);

            foreach ($urlMap as $pageLanguageId => $possibleUrl) {
                if ($possibleUrl == $urlPath) {
                    return PageLanguage::getById($pageLanguageId);
                }
            }

            return null;
        });
    }

    /**
     * @param string $slug
     * @param Page|null $parent
     * @param null $langCode
     * @return null|PageLanguage
     */
    public function getPageLanguageBySlug(string $slug, Page $parent = null, $langCode = null): ?PageLanguage
    {
        $query = (new Builder())
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'p.id = pl.page_id', 'p')
            ->leftJoin(Page::class, 'pa.id = p.parent_id', 'pa')
            ->leftJoin(PageLanguage::class, 'pal.page_id = pa.id AND pal.language_code = pl.language_code', 'pal')
            ->where('pl.url = :url:', ['url' => $slug]);

        if ( ! $parent) {
            $query->andWhere('
                p.parent_id IS NULL OR
                (pa.type = :typeLink: AND pal.url IS NULL) OR
                pa.type = :typeMenu:
            ', [
                'typeLink' => Page::TYPE_LINK,
                'typeMenu' => Page::TYPE_MENU,
            ]);
        } else {
            $query->andWhere('pa.id = ' . $parent->getId());
        }

        if ($langCode) {
            $query->andWhere('pl.' . PageLanguage::FIELD_LANGUAGE_CODE . ' = :langCode:', ['langCode' => $langCode]);
        }

        return $this->dbService->getObject($query);
    }

    /**
     * @return int[]
     */
    public function getPageIdsWithoutUrl(): array
    {
        $query = (new Builder)
            ->columns([PageLanguage::FIELD_PAGE_ID])
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'pl.page_id = p.id', 'p')
            ->groupBy(PageLanguage::FIELD_PAGE_ID)
            ->where(PageLanguage::FIELD_URL . ' IS NULL')
            ->inWhere(Page::FIELD_TYPE, [Page::TYPE_PAGE, Page::TYPE_ALIAS]);

        return $this->dbService->getValues($query);
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return string
     */
    public function createUrlPathByPageLanguage(PageLanguage $pageLanguage): string
    {
        $page = $pageLanguage->page;

        if ($page->key == KikCMSConfig::KEY_PAGE_DEFAULT) {
            if ($pageLanguage->getLanguageCode() == $this->languageService->getDefaultLanguageCode()) {
                return '/';
            }
        }

        if ($page->type == Page::TYPE_LINK) {
            return '/' . $this->getUrlForLinkedPage($pageLanguage);
        }

        $query = (new Builder)
            ->columns(['pl.url'])
            ->from(['p' => Page::class])
            ->join(PageLanguage::class, 'pl.page_id = p.id', 'pl')
            ->where('p.lft < :lft: AND p.rgt > :rgt: AND pl.url IS NOT NULL AND pl.language_code = :code:', [
                'lft'  => $page->lft,
                'rgt'  => $page->rgt,
                'code' => $pageLanguage->getLanguageCode(),
            ])
            ->orderBy('p.lft');

        return '/' . implode('/', array_merge($this->dbService->getValues($query), [$pageLanguage->url]));
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return string
     */
    public function getUrlByPageLanguage(PageLanguage $pageLanguage): string
    {
        $cacheKey = CacheConfig::URL . ':' . $pageLanguage->id;

        return $this->cacheService->cache($cacheKey, function () use ($pageLanguage) {
            return substr($this->createUrlPathByPageLanguage($pageLanguage), 1);
        });
    }

    /**
     * @param int $pageId
     * @param string|null $languageCode
     * @return string
     */
    public function getUrlByPageId(int $pageId, string $languageCode = null): string
    {
        $languageCode = $languageCode ?: $this->translator->getLanguageCode();
        $pageLanguage = $this->pageLanguageService->getByPageId($pageId, $languageCode);

        if ( ! $pageLanguage) {
            return '/page/' . $languageCode . '/' . $pageId;
        }

        return $this->getUrlByPageLanguage($pageLanguage);
    }

    /**
     * @param string $pageKey
     * @param string|null $languageCode
     * @return string
     */
    public function getUrlByPageKey(string $pageKey, string $languageCode = null): string
    {
        if ( ! $page = $this->pageService->getByKey($pageKey)) {
            return '';
        }

        return $this->getUrlByPageId($page->getId(), $languageCode);
    }

    /**
     * Get only the path of the URL, sans leading slash
     *
     * @param string $pageKey
     * @param string|null $languageCode
     * @return string
     */
    public function getUrlPathByPageKey(string $pageKey, string $languageCode = null): string
    {
        $languageCode = $languageCode ?: $this->translator->getLanguageCode();
        $pageLanguage = $this->pageLanguageService->getByPageKey($pageKey, $languageCode);

        if ( ! $pageLanguage) {
            return '/page/' . $languageCode . '/' . $pageKey;
        }

        return $this->getUrlByPageLanguage($pageLanguage);
    }

    /**
     * Get an array with all pages' id, title, url, type for a certain language
     *
     * @param string $languageCode
     * @return array
     */
    public function getUrlData(string $languageCode): array
    {
        $defaultLangCode = $this->languageService->getDefaultLanguageCode();

        $pageUrlDataQuery = (new Builder())
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'p.id = pl.page_id', 'p')
            ->where('pl.language_code = IF(p.type = "menu", :defaultLangCode:, :langCode:)', [
                'langCode'        => $languageCode,
                'defaultLangCode' => $defaultLangCode,
            ])
            ->columns(['p.id', 'p.parent_id', 'pl.name', 'pl.url', 'p.type'])
            ->orderBy('p.lft');

        return $pageUrlDataQuery->getQuery()->execute()->toArray();
    }

    /**
     * @param string $text
     * @return string
     */
    public function toSlug(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        return $text;
    }

    /**
     * Check if the given url exists as child of the given parent, excluding the given page
     *
     * @param string $url
     * @param int $parentId
     * @param PageLanguage $pageLanguage
     * @param string $languageCode
     * @return bool
     */
    public function urlExists(string $url, int $parentId = null, string $languageCode, PageLanguage $pageLanguage = null): bool
    {
        $query = (new Builder())
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'p.id = pl.page_id', 'p')
            ->where('pl.url = :url:', ['url' => $url]);

        if ($parentId) {
            $query->andWhere('p.parent_id = :parentId:', ['parentId' => $parentId]);
        } else {
            $query->andWhere('p.parent_id IS NULL');
        }

        $parentPage = $parentId ? Page::getById($parentId) : null;

        // if the page has a parent page that isn't a menu, we only need to check in the same language
        if ($parentPage && $parentPage->type !== Page::TYPE_MENU) {
            $query->andWhere('pl.language_code = :languageCode:', ['languageCode' => $languageCode]);
        }

        if ($pageLanguage) {
            $query->andWhere('pl.id != :pageLanguageId:', ['pageLanguageId' => $pageLanguage->id]);
        }

        return $query->getQuery()->execute()->count();
    }

    /**
     * Get the name for given pageLanguage, if the parent page is an alias, get the alias' name
     *
     * @param PageLanguage $pageLanguage
     * @return string
     */
    private function getName(PageLanguage $pageLanguage): string
    {
        if ($aliasId = $pageLanguage->page->getAliasId()) {
            $pageLanguage = $this->pageLanguageService->getByPageId($aliasId, $pageLanguage->getLanguageCode());
        }

        return $pageLanguage->name;
    }

    /**
     * @param string $url
     * @return array [pageLanguageId => url]
     */
    private function getPossibleUrlMapByUrl(string $url): array
    {
        $slugs = explode('/', $url);

        $pageLanguageJoin = 'pla.page_id = pa.id AND pla.language_code = pl.language_code AND pla.url IS NOT NULL';

        $query = (new Builder)
            ->columns(['pl.id', 'pla.url'])
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'p.id = pl.page_id', 'p')
            ->leftJoin(Page::class, 'pa.lft < p.lft AND pa.rgt > p.rgt', 'pa')
            ->leftJoin(PageLanguage::class, $pageLanguageJoin, 'pla')
            ->where('pl.url = :url:', ['url' => last($slugs)])
            ->orderBy('pl.id, pa.lft');

        $result = $this->dbService->getKeyedValues($query, true);

        return array_map(function ($s) use ($slugs) {
            return implode('/', array_merge($s, [last($slugs)]));
        }, $result);
    }

    /**
     * @param $pageLanguage
     * @return string
     */
    private function getUrlForLinkedPage(PageLanguage $pageLanguage): string
    {
        $link = $pageLanguage->page->link;

        if (empty($link)) {
            return '';
        }

        if ( ! is_numeric($link)) {
            return $link;
        }

        $pageLanguageLink = $this->pageLanguageService->getByPageId($link, $pageLanguage->getLanguageCode());

        return $this->getUrlByPageLanguage($pageLanguageLink);
    }

    /**
     * @param PageLanguage $pageLang
     * @return bool
     */
    private function urlExistsForPageLanguage(PageLanguage $pageLang): bool
    {
        $parentId = $pageLang->page->parent ? $pageLang->page->parent->id : null;

        return $this->urlExists($pageLang->url, $parentId, $pageLang->language_code, $pageLang);
    }
}