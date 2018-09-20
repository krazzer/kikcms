<?php

namespace KikCMS\Models;

use DateTime;
use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCmsCore\Classes\Model;
use Phalcon\Mvc\Model\Resultset\Simple;

/**
 * @property Page $parent
 * @property Page $linkedPage
 * @property PageLanguage $pageLanguage
 * @property Page[] $aliases
 * @property Simple|PageLanguage[] $pageLanguages
 * @property Simple|PageLanguageContent[] $pageLanguageContents
 * @property Simple|PageContent[] $pageContents
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

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->rgt - $this->lft > 1;
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->belongsTo(self::FIELD_PARENT_ID, Page::class, Page::FIELD_ID, ["alias" => "parent"]);
        $this->hasMany(self::FIELD_ID, Page::class, Page::FIELD_ALIAS, ["alias" => "aliases"]);
        $this->hasMany(self::FIELD_ID, PageLanguage::class, PageLanguage::FIELD_PAGE_ID, ["alias" => "pageLanguages"]);
        $this->hasMany(self::FIELD_ID, PageContent::class, PageContent::FIELD_PAGE_ID, ["alias" => "pageContents"]);
        $this->hasMany(self::FIELD_ID, PageLanguageContent::class, PageLanguageContent::FIELD_PAGE_ID, ["alias" => "pageLanguageContents"]);
        $this->belongsTo(self::FIELD_LINK, Page::class, Page::FIELD_ID, ["alias" => "linkedPage"]);

        $this->hasOne(self::FIELD_ID, PageLanguage::class, PageLanguage::FIELD_PAGE_ID, ["alias" => "pageLanguage"]);

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
        return $this->pageLanguage->name;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return (int) $this->level;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return (int) $this->parent_id;
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
        if ( ! $this->alias) {
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
     * Add relations for each field, and for each language
     */
    private function addPageContentRelations()
    {
        $templateFieldKeys = $this->getTemplateFieldKeys();
        $languages         = $this->getLanguages();

        foreach ($templateFieldKeys as $key) {
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
        $languages = $this->getLanguages();

        foreach ($languages as $language) {
            $this->hasOne(self::FIELD_ID, PageLanguage::class, PageLanguage::FIELD_PAGE_ID, [
                'alias'    => 'pageLanguage' . ucfirst($language->code),
                'defaults' => [PageLanguage::FIELD_LANGUAGE_CODE => $language->code]
            ]);
        }
    }

    /**
     * @return array
     */
    private function getTemplateFieldKeys(): array
    {
        /** @var TemplateFieldsBase $templateFields */
        $templateFields = $this->getDI()->get('templateFields');

        return array_keys($templateFields->getFields());
    }
}