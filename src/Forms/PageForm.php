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
use KikCMS\Classes\WebForm\FieldError;
use KikCMS\Classes\WebForm\Tab;
use KikCMS\DataTables\PagesFlat;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\CacheService;
use KikCMS\Services\DataTable\PagesDataTableService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\TemplateService;
use KikCMS\Services\Pages\UrlService;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Regex;
use Phalcon\Filter\Validation\Validator\StringLength;
use Phalcon\Http\Response;

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
    const FIELD_SLUG   = 'pageLanguage*:slug';
    const SLUG_PATTERN = '/^$|^([0-9a-z\-]+)$/';

    /**
     * @inheritdoc
     */
    protected function initialize(): void
    {
        if ($this->getObject() && $this->getObject()->alias) {
            $this->addHtmlField('alias', null, 'Aliases cannot be edited');
            $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_ALIAS);
            return;
        }

        $this->addTab('Pagina', [
            $this->addTextField('pageLanguage*:name', $this->translator->tl('fields.name'), [new PresenceOf()]),
            $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_PAGE),
        ]);

        $this->addFieldsForCurrentTemplate();

        $urlPatternValidation = new Regex([
            'pattern' => self::SLUG_PATTERN,
            'message' => $this->translator->tl('webform.messages.slug')
        ]);

        $urlValidation = [new PresenceOf(), $urlPatternValidation, new StringLength(["max" => 255])];

        $tabAdvancedFields = [
            $this->getTemplateField(),

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
            $this->addTextField('pageLanguage*:seo_title', $this->translator->tl('dataTable.pages.seo.title'))
                ->setHelpText($this->translator->tl('dataTable.pages.seo.titleHelp')),
            $this->addTextAreaField('pageLanguage*:seo_keywords', $this->translator->tl('dataTable.pages.seo.keywords'))
                ->rows(4)->setHelpText($this->translator->tl('dataTable.pages.seo.keywordsHelp')),
            $this->addTextAreaField('pageLanguage*:seo_description', $this->translator->tl('dataTable.pages.seo.description'))
                ->rows(12)->setHelpText($this->translator->tl('dataTable.pages.seo.descriptionHelp')),
        ];

        $this->addTab('SEO', $tabSeoFields);
        $this->addTab($this->translator->tl('fields.advanced'), $tabAdvancedFields)->setKey('advanced');

        $this->addHiddenField(Page::FIELD_DISPLAY_ORDER);
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

        if( ! preg_match(self::SLUG_PATTERN, $input[self::FIELD_SLUG])){
            return $errorContainer;
        }

        $pageLanguage = $this->getPageLanguage();

        if ($pageLanguage && $parentPageLanguage = $pageLanguage->getParentWithSlug()) {
            $urlPath = $this->urlService->getUrlByPageLanguage($parentPageLanguage) . '/' . $urlPath;
        }

        if ($this->urlService->urlPathExists($urlPath, $pageLanguage)) {
            $slugExistsMessage = $this->translator->tl('dataTables.pages.slugExists');
            $errorContainer->addFieldError(new FieldError(self::FIELD_SLUG, $slugExistsMessage));
        }

        $template = $this->templateService->getByKey($input['template']);

        if (isset($input['key']) && $template->getPageKey() && $template->getPageKey() !== $input['key']) {
            $templatePageKeyMismatchMessage = $this->translator->tl('dataTables.pages.templatePageKeyMismatch', [
                'template' => $template->getName(),
                'key'      => $template->getPageKey(),
            ]);

            $errorContainer->addFieldError(new FieldError('template', $templatePageKeyMismatchMessage));
        }

        return $errorContainer;
    }

    /**
     * @inheritdoc
     */
    protected function onSave(): void
    {
        $this->pageRearrangeService->updateNestedSet();
        $this->cacheService->clearPageCache();
    }

    /**
     * Adds fields for current template
     */
    protected function addFieldsForCurrentTemplate(): void
    {
        $template          = $this->getTemplate();
        $fields            = $this->templateService->getFieldsByTemplate($template);
        $displayConditions = $this->templateFields->getFieldDisplayConditions();

        foreach ($fields as $key => $field) {
            if (array_key_exists($key, $displayConditions)) {
                if ( ! $displayConditions[$key]($this->getObject(), $this)) {
                    continue;
                }
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
     * @inheritDoc
     */
    function successAction(array $input): Response|string|null
    {
        if ( ! $page = $this->getObject()) {
            return parent::successAction($input);
        }

        if ( ! array_key_exists(Page::FIELD_PARENT_ID, $input)) {
            return parent::successAction($input);
        }

        if ($page->parent_id == $input[Page::FIELD_PARENT_ID]) {
            return parent::successAction($input);
        }

        // of the parent has changed, reset the display order
        $input[Page::FIELD_DISPLAY_ORDER] = null;

        return parent::successAction($input);
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

    /**
     * @return Field
     */
    private function getTemplateField(): Field
    {
        if ($this->getDataTable() instanceof PagesFlat && $this->getDataTable()->getTemplate()) {
            return $this->addHiddenField(Page::FIELD_TEMPLATE, $this->getTemplate()->getKey());
        }

        $currentTemplate = $this->getObject()->template ?? null;

        $templateField = $this->addSelectField(Page::FIELD_TEMPLATE, $this->translator->tl('fields.template'),
            $this->templateService->getAvailableNameMap($currentTemplate));

        $templateField->getElement()->setDefault($this->getTemplate()->getKey());

        return $templateField;
    }
}