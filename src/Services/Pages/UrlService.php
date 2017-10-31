<?php

namespace KikCMS\Services\Pages;


use Exception;
use KikCMS\Classes\DbService;
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
 * @property PageLanguageService $pageLanguageService
 * @property LanguageService $languageService
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
     * @param string $url
     * @return null|PageLanguage
     */
    public function getPageLanguageByUrl(string $url): ?PageLanguage
    {
        $cacheKey = CacheConfig::PAGE_LANGUAGE_FOR_URL . ':' . $url;

        return $this->cacheService->cache($cacheKey, function () use ($url) {
            $pageLanguage = null;
            $parent       = null;
            $langCode     = null;

            $slugs = explode('/', $url);

            foreach ($slugs as $slug) {
                $pageLanguage = $this->getPageLanguageBySlug($slug, $parent, $langCode);

                if ( ! $pageLanguage) {
                    return null;
                }

                if ( ! $langCode) {
                    $langCode = $pageLanguage->getLanguageCode();
                }

                if (count($slugs) > 1) {
                    $parent = $pageLanguage->page;
                }
            }

            return $pageLanguage;
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
            ->where('pl.url = :url:', ['url' => $slug]);

        if ( ! $parent) {
            $query->andWhere('p.parent_id IS NULL OR pa.type = :type:', ['type' => Page::TYPE_MENU]);
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
     * @param bool $addLeadingSlash
     * @return string
     */
    public function getUrlByPageLanguage(PageLanguage $pageLanguage, bool $addLeadingSlash = true): string
    {
        $cacheKey = CacheConfig::URL . ':' . $pageLanguage->id;

        $url = $this->cacheService->cache($cacheKey, function () use ($pageLanguage) {
            if($pageLanguage->page->key == KikCMSConfig::KEY_PAGE_DEFAULT){
                if($pageLanguage->getLanguageCode() == $this->languageService->getDefaultLanguageCode()){
                    return '';
                }
            }

            if ($pageLanguage->page->type == Page::TYPE_LINK) {
                return $this->getUrlForLinkedPage($pageLanguage);
            }

            $langCode = $pageLanguage->language_code;
            $parent   = $pageLanguage->page->parent;
            $urlParts = [$pageLanguage->url];

            while ($parent && $parent->type != Page::TYPE_MENU) {
                $pageLanguage = $this->pageLanguageService->getByPage($parent, $langCode);

                if ( ! $pageLanguage) {
                    break;
                }

                $parent     = $pageLanguage->page->parent;
                $urlParts[] = $pageLanguage->url;
            }

            return implode('/', array_reverse($urlParts));
        });

        return ($addLeadingSlash ? '/' : '') . $url;
    }

    /**
     * @param int $pageId
     * @param string $languageCode
     *
     * @return string
     * @throws Exception
     */
    public function getUrlByPageId(int $pageId, string $languageCode): string
    {
        $pageLanguage = $this->pageLanguageService->getByPageId($pageId, $languageCode);

        if ( ! $pageLanguage) {
            return '/page/' . $languageCode . '/' . $pageId;
        }

        return $this->getUrlByPageLanguage($pageLanguage);
    }

    /**
     * @param string $pageKey
     * @param string $languageCode
     * @return string
     */
    public function getUrlByPageKey(string $pageKey, string $languageCode): string
    {
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
     * @param PageLanguage $pageLang
     * @return bool
     */
    private function urlExistsForPageLanguage(PageLanguage $pageLang): bool
    {
        $parentId = $pageLang->page->parent ? $pageLang->page->parent->id : null;

        return $this->urlExists($pageLang->url, $parentId, $pageLang->language_code, $pageLang);
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

        return $this->getUrlByPageLanguage($pageLanguageLink, false);
    }

    /**
     * Get the name for given pageLanguage, if the parent page is an alias, get the alias' name
     *
     * @param PageLanguage $pageLanguage
     * @return string
     */
    private function getName(PageLanguage $pageLanguage): string
    {
        if($aliasId = $pageLanguage->page->getAliasId()){
            $pageLanguage = $this->pageLanguageService->getByPageId($aliasId, $pageLanguage->getLanguageCode());
        }

        return $pageLanguage->name;
    }
}