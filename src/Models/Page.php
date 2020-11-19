<?php declare(strict_types=1);

namespace KikCMS\Models;

use DateTime;
use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Services\DataTable\NestedSetService;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCmsCore\Classes\Model;
use Phalcon\Mvc\Model\Resultset\Simple;

/**
 * @property Page $parent
 * @property Page $linkedPage
 * @property Page $aliasPage
 * @property PageLanguage $pageLanguage
 * @property Page[] $aliases
 * @property Simple|PageLanguage[] $pageLanguages
 * @property Simple|PageLanguageContent[] $pageLanguageContents
 * @property Simple|PageContent[] $pageContents
 * @property Simple|Page[] $children
 */
class Page extends Model
{
    const TABLE = 'cms_page';
    const ALIAS = 'p';

    const FIELD_ID            = 'id';
    const FIELD_TYPE          = 'type';
    const FIELD_PARENT_ID     = 'parent_id';
    const FIELD_ALIAS         = 'alias';
    const FIELD_TEMPLATE      = 'template';
    const FIELD_DISPLAY_ORDER = 'display_order';
    const FIELD_KEY           = 'key';
    const FIELD_LEVEL         = 'level';
    const FIELD_LFT           = 'lft';
    const FIELD_RGT           = 'rgt';
    const FIELD_LINK          = 'link';
    const FIELD_CREATED_AT    = 'created_at';
    const FIELD_UPDATED_AT    = 'updated_at';

    const TYPE_PAGE  = 'page';
    const TYPE_MENU  = 'menu';
    const TYPE_LINK  = 'link';
    const TYPE_ALIAS = 'alias';

    /** @var bool|null set true if the nested set should not be updated */
    private $preventNestedSetUpdate;

    /**
     * Set lft, rgt, level and display_order if empty
     */
    public function beforeSave()
    {
        if ($this->getPageService()->requiresNesting($this)) {
            $this->getNestedSetService()->setAndMakeRoomForNewPage($this);
        }

        if ( ! $this->getDisplayOrder() && $this->getParentId()) {
            $this->resetDisplayOrder();
        }
    }

    /**
     * Remove cache when removing a page
     */
    public function beforeDelete()
    {
        foreach ($this->pageLanguages as $pageLanguage) {
            $this->getPageLanguageService()->removeCache($pageLanguage);
        }

        $offspringAliases = $this->getPageService()->getOffspringAliases($this);

        foreach ($offspringAliases as $aliasPage) {
            $aliasPage->delete();
        }
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->rgt - $this->lft > 1;
    }

    /**
     * Init
     */
    public function initialize()
    {
        parent::initialize();

        /** @var WebsiteSettingsBase $websiteSettings */
        $websiteSettings = $this->getDI()->getShared('websiteSettings');

        $pageClass = $websiteSettings->getPageClass();

        $this->belongsTo(self::FIELD_PARENT_ID, Page::class, Page::FIELD_ID, ["alias" => "parent"]);
        $this->belongsTo(self::FIELD_LINK, Page::class, Page::FIELD_ID, ["alias" => "linkedPage"]);
        $this->hasOne(self::FIELD_ID, PageLanguage::class, PageLanguage::FIELD_PAGE_ID, ["alias" => "pageLanguage"]);
        $this->hasOne(self::FIELD_ALIAS, $pageClass, Page::FIELD_ID, ["alias" => "aliasPage"]);
        $this->hasMany(self::FIELD_ID, Page::class, Page::FIELD_ALIAS, ["alias" => "aliases"]);
        $this->hasMany(self::FIELD_ID, Page::class, Page::FIELD_PARENT_ID, ["alias" => "children"]);
        $this->hasMany(self::FIELD_ID, PageLanguage::class, PageLanguage::FIELD_PAGE_ID, ["alias" => "pageLanguages"]);
        $this->hasMany(self::FIELD_ID, PageContent::class, PageContent::FIELD_PAGE_ID, ["alias" => "pageContents"]);
        $this->hasMany(self::FIELD_ID, PageLanguageContent::class, PageLanguageContent::FIELD_PAGE_ID, [
            "alias" => "pageLanguageContents"
        ]);

        $this->skipAttributesOnCreate([self::FIELD_CREATED_AT, self::FIELD_UPDATED_AT]);
        $this->skipAttributesOnUpdate([self::FIELD_UPDATED_AT]);

        $this->addPageLanguageRelations();
        $this->addPageContentRelations();
    }

    /**
     * @inheritdoc
     * @return Page
     */
    public static function getById($id)
    {
        return parent::getById($id);
    }

    /**
     * @inheritdoc
     * @return Page[]
     */
    public static function getByIdList(array $ids)
    {
        return parent::getByIdList($ids);
    }

    /**
     * @return int|null
     */
    public function getDisplayOrder(): ?int
    {
        if ( ! property_exists($this, self::FIELD_DISPLAY_ORDER)) {
            return null;
        }

        return (int) $this->display_order ?: null;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * Retrieves the name of the page, this only works for single-language applications
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        if ($this->alias) {
            return (string) $this->aliasPage->pageLanguage->name;
        }

        if ( ! $this->pageLanguage) {
            return '';
        }

        return (string) $this->pageLanguage->name;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return (int) $this->level;
    }

    /**
     * @param string $langCode
     * @return PageLanguage|null
     */
    public function getPageLanguageByLangCode(string $langCode): ?PageLanguage
    {
        $pageLanguageRelation = 'pageLanguage' . ucfirst($langCode);

        /** @var PageLanguage|null $pageLanguage */
        $pageLanguage = $this->$pageLanguageRelation;

        return $pageLanguage;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        if ( ! property_exists($this, self::FIELD_PARENT_ID)) {
            return null;
        }

        return (int) $this->parent_id ?: null;
    }

    /**
     * Traverse up the page hierarchy with given languageCode until a parent PageLanguage is found with a slug
     *
     * @param string $langCode
     * @return PageLanguage|null
     */
    public function getParentPageLanguageWithSlugByLangCode(string $langCode): ?PageLanguage
    {
        if ( ! $this->parent) {
            return null;
        }

        $pageLanguage = $this->parent->getPageLanguageByLangCode($langCode);

        if ($pageLanguage && $pageLanguage->getSlug()) {
            return $pageLanguage;
        }

        if ( ! $this->parent->parent) {
            return null;
        }

        return $this->parent->getParentPageLanguageWithSlugByLangCode($langCode);
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return (string) $this->template;
    }

    /**
     * @return int|null
     */
    public function getAliasId(): ?int
    {
        if ( ! property_exists($this, 'alias') || ! $this->alias) {
            return null;
        }

        return (int) $this->alias;
    }

    /**
     * @return DateTime
     */
    public function getCreatedDate(): DateTime
    {
        return new DateTime($this->created_at);
    }

    /**
     * @return int|null
     */
    public function getLink(): ?int
    {
        if ( ! $this->link) {
            return null;
        }

        return (int) $this->link;
    }

    /**
     * If this is an alias, return the id of the alias if not just this id
     * @return int
     */
    public function getRealId(): int
    {
        return $this->getAliasId() ?: $this->getId();
    }

    /**
     * Set a new display order
     */
    public function resetDisplayOrder()
    {
        $this->display_order = $this->getPageRearrangeService()->getMaxDisplayOrder($this->parent) + 1;
    }

    /**
     * @return bool|null
     */
    public function getPreventNestedSetUpdate(): ?bool
    {
        return $this->preventNestedSetUpdate;
    }

    /**
     * @param bool|null $preventNestedSetUpdate
     * @return Page
     */
    public function setPreventNestedSetUpdate(?bool $preventNestedSetUpdate): Page
    {
        $this->preventNestedSetUpdate = $preventNestedSetUpdate;
        return $this;
    }

    /**
     * Add relations for each field, and for each language
     */
    private function addPageContentRelations()
    {
        $templateFieldKeys = $this->getTemplateFieldMap();
        $languages         = $this->getDI()->getShared('languageService')->getLanguages();

        foreach ($templateFieldKeys as $key => $field) {
            // skip fields that aren't content fields
            if ($field instanceof Field && substr($field->getKey(), -6) !== ':value') {
                continue;
            }

            $this->hasOne(self::FIELD_ID, PageContent::class, PageContent::FIELD_PAGE_ID, [
                'alias'    => $key,
                'defaults' => [PageContent::FIELD_FIELD => $key]
            ]);

            foreach ($languages as $language) {
                $this->hasOne(self::FIELD_ID, PageLanguageContent::class, PageLanguageContent::FIELD_PAGE_ID, [
                    'alias'    => $key . ucfirst($language->code),
                    'defaults' => [
                        PageLanguageContent::FIELD_FIELD         => $key,
                        PageLanguageContent::FIELD_LANGUAGE_CODE => $language->code
                    ]
                ]);
            }
        }
    }

    /**
     * Add pageLanguage relations for each language, like pageLanguageEn
     */
    private function addPageLanguageRelations()
    {
        $languages = $this->getDI()->get('languageService')->getLanguages();

        foreach ($languages as $language) {
            $this->hasOne(self::FIELD_ID, PageLanguage::class, PageLanguage::FIELD_PAGE_ID, [
                'alias'    => 'pageLanguage' . ucfirst($language->code),
                'defaults' => [PageLanguage::FIELD_LANGUAGE_CODE => $language->code]
            ]);
        }
    }

    /**
     * @return Field[]
     */
    private function getTemplateFieldMap(): array
    {
        /** @var TemplateFieldsBase $templateFields */
        $templateFields = $this->getDI()->get('templateFields');

        return $templateFields->getFields();
    }

    /**
     * @return NestedSetService
     */
    private function getNestedSetService(): NestedSetService
    {
        return $this->getDI()->get('nestedSetService');
    }

    /**
     * @return PageRearrangeService
     */
    private function getPageRearrangeService(): PageRearrangeService
    {
        return $this->getDI()->get('pageRearrangeService');
    }

    /**
     * @return PageLanguageService
     */
    private function getPageLanguageService(): PageLanguageService
    {
        return $this->getDI()->get('pageLanguageService');
    }

    /**
     * @return PageService
     */
    private function getPageService(): PageService
    {
        return $this->getDI()->get('pageService');
    }

    /**
     * @param int|null $parentId
     * @return $this
     */
    public function setParentId(?int $parentId): Page
    {
        $this->parent_id = $parentId;
        return $this;
    }

    /**
     * @param int|null $displayOrder
     * @return $this
     */
    public function setDisplayOrder(?int $displayOrder)
    {
        $this->display_order = $displayOrder;
        return $this;
    }
}