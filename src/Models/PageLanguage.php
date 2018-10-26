<?php

namespace KikCMS\Models;

use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\UrlService;
use KikCmsCore\Classes\Model;

/**
 * @property Page $page
 */
class PageLanguage extends Model
{
    const TABLE = 'cms_page_language';
    const ALIAS = 'pl';

    const FIELD_ID              = 'id';
    const FIELD_PAGE_ID         = 'page_id';
    const FIELD_LANGUAGE_CODE   = 'language_code';
    const FIELD_ACTIVE          = 'active';
    const FIELD_NAME            = 'name';
    const FIELD_URL             = 'url';
    const FIELD_SEO_TITLE       = 'seo_title';
    const FIELD_SEO_DESCRIPTION = 'seo_description';
    const FIELD_SEO_KEYWORDS    = 'seo_keywords';

    /** @var string|null */
    private $aliasName;

    /** @var string|null */
    private $url;

    /**
     * Remove cache
     */
    public function beforeDelete()
    {
        /** @var PageLanguageService $pageLanguageService */
        $pageLanguageService = $this->getDI()->get('pageLanguageService');

        $pageLanguageService->removeCache($this);
    }

    /**
     * Create URL if needed
     */
    public function beforeSave()
    {
        // url is set, so do nothing
        if ($this->url) {
            return;
        }

        // menu's and links don't require urls
        if (in_array($this->page->type, [Page::TYPE_MENU, Page::TYPE_LINK])) {
            return;
        }

        $this->url = $urlPath = $this->getUrlService()->toSlug($this->getName());

        if ($parent = $this->getParentWithSlug()) {
            $urlPath = $this->getUrlService()->getUrlByPageLanguage($parent) . '/' . $urlPath;
        }

        if ($this->getUrlService()->urlPathExists($urlPath, $this)) {
            $this->getUrlService()->deduplicateUrl($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->belongsTo("page_id", Page::class, "id", ["alias" => "page"]);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * @return int
     */
    public function getPageId(): int
    {
        return (int) $this->page_id;
    }

    /**
     * Retrieve the parent pageLanguage, which has the same languageCode
     *
     * @return PageLanguage|null
     */
    public function getParent(): ?PageLanguage
    {
        if ( ! $parentPage = $this->page->parent) {
            return null;
        }

        return $parentPage->getPageLanguageByLangCode($this->getLanguageCode());
    }

    /**
     * Traverse up the page hierarchy (with this objects' language) until a parent PageLanguage is found with a slug
     *
     * @return null|PageLanguage
     */
    public function getParentWithSlug(): ?PageLanguage
    {
        return $this->page->getParentPageLanguageWithSlugByLangCode($this->getLanguageCode());
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return (string) $this->language_code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if ($this->aliasName && empty($this->name)) {
            return $this->aliasName;
        }

        return (string) $this->name;
    }

    /**
     * @param string $name
     */
    public function setAliasName(string $name)
    {
        $this->aliasName = $name;
    }

    /**
     * @return null|string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param null|string $url
     * @return PageLanguage
     */
    public function setUrl(?string $url): PageLanguage
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return UrlService
     */
    private function getUrlService(): UrlService
    {
        return $this->getDI()->get('urlService');
    }
}