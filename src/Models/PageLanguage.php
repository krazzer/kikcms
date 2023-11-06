<?php declare(strict_types=1);

namespace KikCMS\Models;

use KikCMS\Classes\Phalcon\IniConfig;
use KikCMS\Objects\Redirect\RedirectService;
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
    const FIELD_SLUG            = 'slug';
    const FIELD_SEO_TITLE       = 'seo_title';
    const FIELD_SEO_DESCRIPTION = 'seo_description';
    const FIELD_SEO_KEYWORDS    = 'seo_keywords';

    /** @var string|null */
    private $slug;

    /** @var Page|null */
    private $aliasPage;

    /**
     * @inheritDoc
     * @return PageLanguage|null
     */
    public static function getById($id): ?PageLanguage
    {
        return parent::getById($id);
    }

    /**
     * Remove cache
     */
    public function beforeDelete(): void
    {
        /** @var PageLanguageService $pageLanguageService */
        $pageLanguageService = $this->getDI()->get('pageLanguageService');

        $pageLanguageService->removeCache($this);
    }

    /**
     * Create URL if needed
     */
    public function beforeSave(): void
    {
        /** @var PageLanguageService $pageLanguageService */
        $pageLanguageService = $this->getDI()->get('pageLanguageService');

        $pageLanguageService->checkAndUpdateSlug($this);
    }

    /**
     * Check if the slug has changed
     */
    public function beforeUpdate()
    {
        /** @var IniConfig $config */
        $config = $this->getDI()->get('config');

        if( ! $config->application->autoredirect){
            return;
        }

        if ( ! $this->hasChanged(self::FIELD_SLUG)) {
            return;
        }

        $previousPageLanguage = self::getById($this->getId());

        /** @var UrlService $urlService */
        $urlService = $this->getDI()->get('urlService');

        /** @var RedirectService $redirectService */
        $redirectService = $this->getDI()->get('redirectService');

        $previousUrlPath = $urlService->createUrlPathByPageLanguage($previousPageLanguage);
        $urlPath         = $urlService->createUrlPathByPageLanguage($this);

        $redirectService->add($previousUrlPath, $urlPath, $this->getId());
    }

    /**
     * Initialize relations
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->keepSnapshots(true);

        $pageClass = $this->getDI()->get('websiteSettings')->getPageClass() ?: Page::class;

        $this->belongsTo("page_id", $pageClass, "id", ["alias" => "page"]);
    }

    /**
     * @return bool
     */
    public function hasAliasPage(): bool
    {
        return (bool) $this->aliasPage;
    }

    /**
     * @return Page
     */
    public function getAliasPage(): Page
    {
        return $this->aliasPage ?: $this->page;
    }

    /**
     * @return int
     */
    public function getAliasPageId(): int
    {
        return $this->aliasPage ? $this->aliasPage->getId() : $this->getPageId();
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
        return (string) $this->name;
    }

    /**
     * @return null|string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param null|string $slug
     * @return PageLanguage
     */
    public function setSlug(?string $slug): PageLanguage
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @param string|null $name
     * @return PageLanguage
     */
    public function setName(?string $name): PageLanguage
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param Page|null $page
     * @return $this
     */
    public function setAliasPage(?Page $page): PageLanguage
    {
        $this->aliasPage = $page;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasId(): bool
    {
        return property_exists($this, self::FIELD_ID);
    }
}