<?php

namespace KikCMS\Services\Pages;


use Exception;
use KikCMS\Classes\DbService;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\CacheService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 * @property CacheService $cacheService
 * @property PageLanguageService $pageLanguageService
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
        $pageLanguages = $this->pageLanguageService->getAllByPageId($pageId);

        /** @var PageLanguage $pageLanguage */
        foreach ($pageLanguages as $pageLanguage) {
            $pageLanguage->url = $this->toSlug($pageLanguage->name);

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
     * @return PageLanguage|null
     */
    public function getPageLanguageByUrl(string $url)
    {
        $cacheKey = CacheConfig::PAGE_LANGUAGE_FOR_URL . ':' . $url;

        return $this->cacheService->cache($cacheKey, function () use ($url) {
            $pageLanguage = null;
            $parent       = null;

            $slugs = explode('/', $url);

            foreach ($slugs as $slug) {
                $pageLanguage = $this->getPageLanguageBySlug($slug, $parent);

                if ( ! $pageLanguage) {
                    return null;
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
     * @return PageLanguage|null
     */
    public function getPageLanguageBySlug(string $slug, Page $parent = null)
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

        return $query->getQuery()->execute()->getFirst();
    }

    /**
     * @return int[]
     */
    public function getPageIdsWithoutUrl(): array
    {
        $query = (new Builder())
            ->columns([PageLanguage::FIELD_PAGE_ID])
            ->from(PageLanguage::class)
            ->groupBy(PageLanguage::FIELD_PAGE_ID)
            ->where(PageLanguage::FIELD_URL . ' IS NULL');

        return $this->dbService->getValues($query);
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return string
     */
    public function getUrlByPageLanguage(PageLanguage $pageLanguage): string
    {
        $cacheKey = CacheConfig::URL . ':' . $pageLanguage->id;

        return $this->cacheService->cache($cacheKey, function () use ($pageLanguage) {
            $langCode = $pageLanguage->language_code;
            $parent   = $pageLanguage->page->parent;
            $urlParts = [$pageLanguage->url];

            while ($parent && $parent->type != Page::TYPE_MENU) {
                $pageLanguage = $this->pageLanguageService->getByPage($parent, $langCode);
                $parent       = $pageLanguage->page->parent;
                $urlParts[]   = $pageLanguage->url;
            }

            return implode('/', array_reverse($urlParts));
        });
    }

    /**
     * @param int $pageId
     * @return string
     * @throws Exception
     */
    public function getUrlByPageId(int $pageId): string
    {
        $langCode     = $this->translator->getLanguageCode();
        $pageLanguage = $this->pageLanguageService->getByPageId($pageId, $langCode);

        if ( ! $pageLanguage) {
            throw new Exception("No page found in the current language for page id " . $pageId);
        }

        return $this->getUrlByPageLanguage($pageLanguage);
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
     * @param string $text
     * @return string
     */
    private function toSlug(string $text): string
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
     * @param PageLanguage $pageLang
     * @return bool
     */
    private function urlExistsForPageLanguage(PageLanguage $pageLang)
    {
        $parentId = $pageLang->page->parent ? $pageLang->page->parent->id : null;

        return $this->urlExists($pageLang->url, $parentId, $pageLang->language_code, $pageLang);
    }
}