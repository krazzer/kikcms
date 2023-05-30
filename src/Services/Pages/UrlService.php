<?php declare(strict_types=1);

namespace KikCMS\Services\Pages;


use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Classes\Translator;
use KikCMS\Config\UrlConfig;
use KikCmsCore\Services\DbService;
use KikCMS\Config\CacheConfig;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\CacheService;
use KikCMS\Services\LanguageService;
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
            $pageLanguage->setSlug($this->toSlug($this->getName($pageLanguage)));

            if ($this->urlExistsForPageLanguage($pageLanguage)) {
                $this->deduplicateAndStoreNewUrl($pageLanguage);
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

        $newUrl = $currentUrl = $this->getUrlByPageLanguage($pageLanguage);

        while ($this->urlPathExists($newUrl)) {
            $newUrl = $currentUrl . '-' . $newUrlIndex;
            $newUrlIndex++;
        }

        // no new url was created, so no need to save
        if ($newUrlIndex === 1) {
            return;
        }

        $pageLanguage->setSlug(basename($newUrl));
    }

    /**
     * @param PageLanguage $pageLanguage
     */
    public function deduplicateAndStoreNewUrl(PageLanguage $pageLanguage)
    {
        $this->deduplicateUrl($pageLanguage);

        $pageLanguage->save();

        $this->cacheService->clearPageCache();
    }

    /**
     * @param string $urlPath
     * @param bool $existsCheck
     * @return null|PageLanguage
     */
    public function getPageLanguageByUrlPath(string $urlPath, bool $existsCheck = false): ?PageLanguage
    {
        $urlPath  = $this->removeLeadingSlash($urlPath);
        $cacheKey = CacheConfig::PAGE_LANGUAGE_FOR_URL . CacheConfig::SEPARATOR . str_replace('/', '_', $urlPath);

        $cached = $this->cacheService->cache($cacheKey, function () use ($urlPath, $existsCheck) {
            if ($existsCheck && $this->existingPageCacheService->exists($urlPath) === false) {
                return null;
            }

            $urlMap = $this->getPossibleUrlMapByUrl($urlPath);

            foreach ($urlMap as $key => $possibleUrl) {
                if ($possibleUrl == $urlPath) {
                    return $this->getPageLangAndAliasByKey($key);
                }
            }

            return null;
        });

        if ( ! $cached || ! $cached[0]) {
            return null;
        }

        return $cached[0]->setAliasPage($cached[1]);
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
            ->where(PageLanguage::FIELD_SLUG . ' IS NULL')
            ->inWhere(Page::FIELD_TYPE, [Page::TYPE_PAGE, Page::TYPE_ALIAS]);

        return $this->dbService->getValues($query);
    }

    /**
     * @param PageLanguage $pageLanguage
     * @param Page|null $aliasPage
     * @return string
     */
    public function createUrlPathByPageLanguage(PageLanguage $pageLanguage, Page $aliasPage = null): string
    {
        $page      = $pageLanguage->page;
        $aliasPage = $aliasPage ?: $page;

        if ($page->key == KikCMSConfig::KEY_PAGE_DEFAULT) {
            if ($pageLanguage->getLanguageCode() == $this->languageService->getDefaultLanguageCode()) {
                return '/';
            }
        }

        if ($page->type == Page::TYPE_LINK) {
            return $this->getUrlForLinkedPage($pageLanguage);
        }

        $query = (new Builder)
            ->columns(['pl.slug'])
            ->from(['p' => Page::class])
            ->join(PageLanguage::class, 'pl.page_id = p.id', 'pl')
            ->where('p.lft < :lft: AND p.rgt > :rgt: AND pl.slug IS NOT NULL AND pl.language_code = :code:', [
                'lft'  => $aliasPage->lft,
                'rgt'  => $aliasPage->rgt,
                'code' => $pageLanguage->getLanguageCode(),
            ])
            ->orderBy('p.lft');

        return '/' . implode('/', array_merge($this->dbService->getValues($query), [$pageLanguage->getSlug()]));
    }

    /**
     * @param PageLanguage $pageLanguage
     * @param Page|null $aliasPage
     * @return string
     */
    public function getUrlByPageLanguage(PageLanguage $pageLanguage, Page $aliasPage = null): string
    {
        // hasn't been stored yet, so can't be cached
        if ( ! isset($pageLanguage->id)) {
            return $this->createUrlPathByPageLanguage($pageLanguage, $aliasPage);
        }

        $cacheKey = CacheConfig::getUrlKeyByPageLang($pageLanguage);

        if ($aliasPage && $aliasPage->getId() !== $pageLanguage->getPageId()) {
            $cacheKey .= CacheConfig::ALIAS_PREFIX . $aliasPage->getId();
        }

        return (string) $this->cacheService->cache($cacheKey, function () use ($pageLanguage, $aliasPage) {
            return $this->createUrlPathByPageLanguage($pageLanguage, $aliasPage);
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

        $aliasPage = $pageLanguage->hasAliasPage() ? $pageLanguage->getAliasPage() : null;

        return $this->getUrlByPageLanguage($pageLanguage, $aliasPage);
    }

    /**
     * @param string $pageKey
     * @param string|null $languageCode
     * @return string
     */
    public function getUrlByPageKey(string $pageKey, string $languageCode = null): string
    {
        $cacheKey = CacheConfig::URL_FOR_KEY . CacheConfig::SEPARATOR . $pageKey;

        if ($languageCode) {
            $cacheKey .= CacheConfig::SEPARATOR . $languageCode;
        }

        return $this->cacheService->cache($cacheKey, function () use ($pageKey, $languageCode) {
            if ( ! $page = $this->pageService->getByKey($pageKey)) {
                return '';
            }

            return $this->getUrlByPageId($page->getId(), $languageCode);
        });
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
            ->columns(['p.id', 'p.parent_id', 'pl.name', 'pl.slug', 'p.type'])
            ->orderBy('p.lft');

        return $pageUrlDataQuery->getQuery()->execute()->toArray();
    }

    /**
     * Get all URLs for pages
     * @param bool $addAliases
     * @return array
     */
    public function getUrls(bool $addAliases = false): array
    {
        $languages = $this->languageService->getLanguages(true);
        $links     = [];

        foreach ($languages as $language) {
            $languageLinks = $this->getUrlsByLangCode($language->getCode(), $addAliases);
            $links         = array_merge($links, $languageLinks);
        }

        sort($links);

        return $links;
    }

    /**
     * @param string $langCode
     * @param bool $addAliases
     * @return array
     */
    public function getUrlsByLangCode(string $langCode, bool $addAliases = false): array
    {
        $types = $addAliases ? [Page::TYPE_PAGE, Page::TYPE_ALIAS] : [Page::TYPE_PAGE];

        $urlColumn = "CONCAT_WS('/', '', GROUP_CONCAT_EXT(pla.slug, ac.level, '/'), pl.slug)";

        $query = (new Builder)
            ->columns(["IF(p.key = '" . KikCMSConfig::KEY_PAGE_DEFAULT . "', '/', " . $urlColumn . ")"])
            ->from(['p' => Page::class])
            ->leftJoin(Page::class, 'ac.lft < p.lft AND ac.rgt > p.rgt', 'ac')
            ->leftJoin(PageLanguage::class, 'pla.page_id = ac.id AND pla.language_code = "' . $langCode . '"', 'pla')
            ->leftJoin(PageLanguage::class, 'pl.page_id = IFNULL(p.alias, p.id) AND pl.language_code = "' . $langCode .
                '"', 'pl')
            ->inWhere('p.type', $types)
            ->andWhere('p.key IS NULL OR p.key != :p:', ['p' => KikCMSConfig::KEY_PAGE_NOT_FOUND])
            ->groupBy('p.id');

        return $this->dbService->getValues($query);
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

        return strtolower($text);
    }

    /**
     * Check whether given urlPath already exists, excluding given PageLanguage
     *
     * @param string $urlPath
     * @param PageLanguage|null $pageLang
     * @return bool
     */
    public function urlPathExists(string $urlPath, PageLanguage $pageLang = null): bool
    {
        if ( ! $existingPageLang = $this->getPageLanguageByUrlPath($urlPath)) {
            return false;
        }

        if ( ! $pageLang || ! isset($pageLang->id) || ! property_exists($pageLang, PageLanguage::FIELD_ID)) {
            return true;
        }

        return $existingPageLang->id !== $pageLang->id;
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return bool
     */
    public function urlExistsForPageLanguage(PageLanguage $pageLanguage): bool
    {
        $urlPath = $this->getUrlByPageLanguage($pageLanguage);

        return $this->urlPathExists($urlPath, $pageLanguage);
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

        return (string) $pageLanguage->name;
    }

    /**
     * @param string $url
     * @return array [pageLanguageId => [url]]
     */
    private function getPossibleUrlMapByUrl(string $url): array
    {
        $slugs = explode('/', $url);

        $pageLanguageJoin = 'pla.page_id = pa.id AND pla.language_code = pl.language_code AND pla.slug IS NOT NULL';

        $query = (new Builder)
            ->columns(['CONCAT(pl.id, IF(p.alias, CONCAT("' . UrlConfig::ALIAS_SEP . '", p.id), ""))', 'pla.slug AS slug'])
            ->from(['pl' => PageLanguage::class])
            ->join(Page::class, 'IFNULL(p.alias, p.id) = pl.page_id', 'p')
            ->leftJoin(Page::class, 'pa.lft < p.lft AND pa.rgt > p.rgt', 'pa')
            ->leftJoin(PageLanguage::class, $pageLanguageJoin, 'pla')
            ->where('pl.slug = :slug:', ['slug' => last($slugs)])
            ->orderBy('pl.id, pa.lft');

        $result = $this->dbService->getKeyedValues($query, true);

        return array_map(function ($s) use ($slugs) {
            return implode('/', array_merge($s, [last($slugs)]));
        }, $result);
    }

    /**
     * @param PageLanguage $pageLanguage
     * @return string
     */
    public function getUrlForLinkedPage(PageLanguage $pageLanguage): string
    {
        $link = (string) $pageLanguage->page->link;

        if (empty($link)) {
            return '';
        }

        if ( ! is_numeric($link)) {
            if ($this->isExternal($link)) {
                return $link;
            }

            if (substr($link, 0, 1) !== '/') {
                return '/' . $link;
            }

            return $link;
        }

        $pageLanguageLink = $this->pageLanguageService->getByPageId((int) $link, $pageLanguage->getLanguageCode());

        $page = Page::getById($pageLanguageLink->getPageId());

        // you cannot link to a link
        if ($page->type == Page::TYPE_LINK) {
            return '';
        }

        return $this->getUrlByPageLanguage($pageLanguageLink);
    }

    /**
     * @param string $urlPath
     * @return string
     */
    private function removeLeadingSlash(string $urlPath): string
    {
        if (substr($urlPath, 0, 1) == '/') {
            $urlPath = substr($urlPath, 1);
        }

        return $urlPath;
    }

    /**
     * Return the PageLanguage and Page
     * Page is only filled if the page is an alias and not the PageLanguage's Page
     *
     * @param string|int $key the is is either the pageLanguageId, or the pageLanguageId + aliasId concatenated with 'a'
     * @return array [PageLanguage, ?Page]
     */
    private function getPageLangAndAliasByKey($key): array
    {
        if (is_string($key) && strstr($key, UrlConfig::ALIAS_SEP)) {
            $pageLanguageIdParts = explode(UrlConfig::ALIAS_SEP, $key);

            $pageLanguage = PageLanguage::getById($pageLanguageIdParts[0]);
            $aliasPage    = Page::getById($pageLanguageIdParts[1]);

            return [$pageLanguage, $aliasPage];
        } else {
            return [PageLanguage::getById($key), null];
        }
    }

    /**
     * @param string|int $url
     * @return bool
     */
    public function isExternal($url): bool
    {
        $components = parse_url($url);
        return ! empty($components['host']) && strcasecmp($components['host'], $_SERVER['HTTP_HOST']);
    }
}