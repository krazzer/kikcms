<?php

namespace KikCMS\Services\Pages;


use KikCMS\Classes\DbService;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use Phalcon\Cache\Backend;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 * @property Backend $cache
 */
class UrlService extends Injectable
{
    /**
     * Remove all cached urls
     */
    public function clearUrlCache()
    {
        $urlCacheKeys = $this->cache->queryKeys(CacheConfig::PAGE_LANGUAGE_FOR_URL);

        foreach ($urlCacheKeys as $cacheKey) {
            $this->cache->delete($cacheKey);
        }
    }

    /**
     * Check if the given url exists as child of the given parent, excluding the given page
     *
     * @param string $url
     * @param int $parentId
     * @param PageLanguage $pageLanguage
     * @return bool
     */
    public function urlExists(string $url, int $parentId = null, PageLanguage $pageLanguage = null): bool
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

        if ($pageLanguage) {
            $query->andWhere('pl.id != :pageLanguageId:', ['pageLanguageId' => $pageLanguage->id]);
        }

        return $query->getQuery()->execute()->count();
    }

    /**
     * @param PageLanguage $pageLanguage
     */
    public function deduplicateUrl(PageLanguage $pageLanguage)
    {
        $newUrlIndex = 1;

        $newUrl = $pageLanguage->url . '-' . $newUrlIndex;

        while ($this->urlExists($newUrl, $pageLanguage->page->parent_id, $pageLanguage)) {
            $newUrlIndex++;
            $newUrl = $pageLanguage->url . '-' . $newUrlIndex;
        }

        $pageLanguage->url = $newUrl;
        $pageLanguage->save();

        $this->clearUrlCache();
    }

    /**
     * @param string $url
     * @return PageLanguage|null
     */
    public function getPageLanguageByUrl(string $url)
    {
        $cacheKey = CacheConfig::PAGE_LANGUAGE_FOR_URL . ':' . $url;

        if ($this->cache->exists($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

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

        if ($pageLanguage) {
            $this->cache->save($cacheKey, $pageLanguage, CacheConfig::ONE_DAY);
        }

        return $pageLanguage;
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
     * @param PageLanguage $pageLanguage
     * @return string
     */
    public function getUrlByPageLanguage(PageLanguage $pageLanguage): string
    {
        $langCode = $pageLanguage->language_code;
        $urlParts = [$pageLanguage->url];

        while ($pageLanguage->page->parent && $pageLanguage->page->parent->type != Page::TYPE_MENU) {
            var_dump($pageLanguage->page->parent->id, $langCode);
            $pageLanguage = PageLanguage::findFirst([
                'conditions' => 'page_id = :pageId: AND language_code = :langCode:',
                'bind'       => [
                    'pageId'   => $pageLanguage->page->parent->id,
                    'langCode' => $langCode
                ]
            ]);

            $urlParts[] = $pageLanguage->url;
        }

        return implode('/', array_reverse($urlParts));
    }
}