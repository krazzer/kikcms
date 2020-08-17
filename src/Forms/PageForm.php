<?php declare(strict_types=1);

namespace KikCMS\Forms;


use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\Page\Template;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer;
use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\Tab;
use KikCMS\DataTables\PagesFlat;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\CacheService;
use KikCMS\Services\DataTable\PagesDataTableService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\TemplateService;
use KikCMS\Services\Pages\UrlService;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength;

/**
 * @property TemplateService $templateService
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 * @property CacheService $cacheService
 * @property TemplateFieldsBase $templateFields
 * @property AccessControl $acl
 * @property PagesDataTableService $pagesDataTableService
 * @property WebsiteSettingsBase $websiteSettings
 */
class PageForm extends DataForm
{
    const FIELD_SLUG = 'pageLanguage*:slug';

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        if ($this->getObject() && $this->getObject()->alias) {
            $this->addHtmlField('alias', null, 'Aliases cannot be edited');
            return;
        }

        $this->addTab('Pagina', [
            $this->addTextField('pageLanguage*:name', $this->translator->tl('fields.name'), [new PresenceOf()]),
            $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_PAGE),
        ]);

        $this->addFieldsForCurrentTemplate();

        $urlPatternValidation = new Regex([
            'pattern' => '/^$|^([0-9a-z\-]+)$/',
            'message' => $this->translator->tl('webform.messages.slug')
        ]);

        $urlValidation = [new PresenceOf(), $urlPatternValidation, new StringLength(["max" => 255])];

        if ($this->getDataTable() instanceof PagesFlat && $this->getDataTable()->getTemplate()) {
            $templateField = $this->addHiddenField(Page::FIELD_TEMPLATE, $this->getTemplate()->getKey());
        } else {
            $templateField = $this->addSelectField(Page::FIELD_TEMPLATE, $this->translator->tl('fields.template'),
                $this->templateService->getNameMap());
            $templateField->getElement()->setDefault($this->getTemplate()->getKey());
        }

        $tabAdvancedFields = [
            $templateField,

            $this->addTextField(self::FIELD_SLUG, $this->translator->tl('fields.slug'), $urlValidation)
                ->setPlaceholder($this->translator->tl('dataTables.pages.slugPlaceholder'))
                ->setHelpText($this->translator->tl('pages.slugHelpText')),

            $this->addCheckboxField('pageLanguage*:' . PageLanguage::FIELD_ACTIVE, $this->translator->tl('fields.active'))
                ->setDefault(1)
        ];

        if ($this->acl->allowed(Permission::PAGE_KEY, Permission::ACCESS_EDIT)) {
            $keyField = $this->addTextField(Page::FIELD_KEY, $this->translator->tl('fields.key'), [
                $urlPatternValidation,
                new StringLength(["max" => 32])
            ]);

            $tabAdvancedFields = array_add_after_key($tabAdvancedFields, 0, 'key', $keyField);
        }

        $tabSeoFields = [
            $this->addTextField('pageLanguage*:seo_title', 'SEO titel'),
            $this->addTextAreaField('pageLanguage*:seo_keywords', 'SEO sleutelwoorden')->rows(4),
            $this->addTextAreaField('pageLanguage*:seo_description', 'SEO omschrijving')->rows(12),
        ];

        $this->addTab('SEO', $tabSeoFields);
        $this->addTab($this->translator->tl('fields.advanced'), $tabAdvancedFields)->setKey('advanced');
    }

    /**
     * Overwrite to make sure the template is set when changed
     *
     * @inheritdoc
     */
    public function getEditData(): array
    {
        $editData = parent::getEditData();

        $editData[Page::FIELD_TEMPLATE] = $this->getTemplate()->getKey();

        return $editData;
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return $this->websiteSettings->getPageClass();
    }

    /**
     * @inheritdoc
     */
    public function validate(array $input): ErrorContainer
    {
        $errorContainer = parent::validate($input);

        if ($input['type'] == Page::TYPE_MENU && ! $this->acl->allowed(Permission::PAGE_MENU)) {
            $errorContainer->addFormError($this->translator->tl('permissions.editMenus'));
        }

        if ($input['type'] !== Page::TYPE_PAGE) {
            return $errorContainer;
        }

        if ( ! $urlPath = $input[self::FIELD_SLUG]) {
            return $errorContainer;
        }

        $pageLanguage = $this->getPageLanguage();

        if ($pageLanguage && $parentPageLanguage = $pageLanguage->getParentWithSlug()) {
            $urlPath = $this->urlService->getUrlByPageLanguage($parentPageLanguage) . '/' . $urlPath;
        }

        if ($this->urlService->urlPathExists($urlPath, $pageLanguage)) {
            $errorContainer->addFieldError(self::FIELD_SLUG, $this->translator->tl('dataTables.pages.slugExists'));
        }

        return $errorContainer;
    }

    /**
     * @inheritdoc
     */
    protected function onSave()
    {
        $this->cacheService->clearPageCache();
    }

    /**
     * Adds fields for current template
     */
    protected function addFieldsForCurrentTemplate()
    {
        $template          = $this->getTemplate();
        $fields            = $this->templateService->getFieldsByTemplate($template);
        $displayConditions = $this->templateFields->getFieldDisplayConditions();

        foreach ($fields as $key => $field) {
            if (array_key_exists($key, $displayConditions) && ! $displayConditions[$key]($this->getObject())) {
                continue;
            }

            switch (true) {
                case $field instanceof Field:
                    // if the current page is an alias, prefix the relationKey
                    if ($this->getObject() && $this->getObject()->alias) {
                        $field->setKey('aliasPage:' . $field->getKey());
                    }

                    $this->addField($field, $this->tabs[0]);
                break;

                case $field instanceof Tab:
                    $tabFields = [];

                    foreach ($field->getFieldMap() as $tabField) {
                        $tabFields[] = $this->addField($tabField);
                    }

                    $this->addTab($field->getName(), $tabFields);
                break;

                case $field instanceof FieldTransformer:
                    $this->addFieldTransformer($field);
                break;
            }
        }
    }

    /**
     * @return Template|null
     */
    protected function getTemplate(): ?Template
    {
        return $this->pagesDataTableService->getTemplate($this);
    }

    /**
     * @return null|PageLanguage
     */
    private function getPageLanguage(): ?PageLanguage
    {
        $pageId       = $this->getFilters()->getEditId();
        $languageCode = $this->getFilters()->getLanguageCode();

        if ( ! $pageId) {
            return null;
        }

        return $this->pageLanguageService->getByPageId($pageId, $languageCode);
    }
}