<?php

namespace KikCMS\Forms;


use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCMS\Classes\Page\Template;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\CacheService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\TemplateService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\Website\WebsiteService;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength;

/**
 * @property TemplateService $templateService
 * @property PageLanguageService $pageLanguageService
 * @property UrlService $urlService
 * @property CacheService $cacheService
 * @property WebsiteService $websiteService
 * @property TemplateFieldsBase $templateFields
 * @property AccessControl $acl
 */
class PageForm extends DataForm
{
    /** @inheritdoc */
    protected $saveCreatedAt = true;

    /** @inheritdoc */
    protected $saveUpdatedAt = true;

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTab('Pagina', [
            $this->addTextField(PageLanguage::FIELD_NAME, $this->translator->tl('fields.name'), [new PresenceOf()])
                ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true),
            $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_PAGE),
        ]);

        $this->addFieldsForCurrentTemplate();

        $urlPatternValidation = new Regex([
            'pattern' => '/^$|^([0-9a-z\-]+)$/',
            'message' => $this->translator->tl('webform.messages.slug')
        ]);

        $urlValidation = [new PresenceOf(), $urlPatternValidation, new StringLength(["max" => 255])];

        $templateField = $this->addSelectField(Page::FIELD_TEMPLATE, $this->translator->tl('fields.template'), $this->templateService->getNameMap());
        $templateField->getElement()->setDefault($this->getTemplate()->getKey());

        $tabAdvancedFields = [
            $templateField,

            $this->addTextField(PageLanguage::FIELD_URL, $this->translator->tl('fields.url'), $urlValidation)
                ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true)
                ->setPlaceholder($this->translator->tl('dataTables.pages.urlPlaceholder')),

            $this->addCheckboxField(PageLanguage::FIELD_ACTIVE, $this->translator->tl('fields.active'))
                ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true)
                ->setDefault(1)
        ];

        if ($this->acl->allowed(Permission::PAGE_KEY, Permission::ACCESS_TYPE_EDIT)) {
            $keyField = $this->addTextField(Page::FIELD_KEY, $this->translator->tl('fields.key'), [
                $urlPatternValidation,
                new StringLength(["max" => 32])
            ]);

            $tabAdvancedFields = array_add_after_key($tabAdvancedFields, 0, 'key', $keyField);
        }

        $this->addTab($this->translator->tl('fields.advanced'), $tabAdvancedFields);
    }

    /**
     * Overwrite to make sure the template is set when changed
     *
     * @inheritdoc
     */
    public function getEditData(): array
    {
        $editData = parent::getEditData();
        $pageId   = $this->getFilters()->getEditId();

        $defaultLangPage     = $this->pageLanguageService->getByPageId($pageId);
        $defaultLangPageName = $defaultLangPage ? $defaultLangPage->name : '';

        $editData[Page::FIELD_TEMPLATE] = $this->getTemplate()->getKey();

        $editData['pageName'] = $editData['name'] ?: $defaultLangPageName;

        return $editData;
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Page::class;
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

        if ( ! $url = $input['url']) {
            return $errorContainer;
        }

        $parentId     = $this->getParentId();
        $pageLanguage = $this->getPageLanguage();
        $languageCode = $this->getFilters()->getLanguageCode();

        if ($this->urlService->urlExists($url, $parentId, $languageCode, $pageLanguage)) {
            $errorContainer->addFieldError('url', $this->translator->tl('dataTables.pages.urlExists'));
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
    private function addFieldsForCurrentTemplate()
    {
        $fields = $this->templateService->getFieldsByTemplate($this->getTemplate());

        /** @var Field $field */
        foreach ($fields as $field) {
            $this->addField($field, $this->tabs[0]);
        }
    }

    /**
     * @return Template|null
     */
    protected function getTemplate(): ?Template
    {
        $templateKey = $this->request->getPost('template');

        if ($templateKey) {
            return $this->templateService->getByKey($templateKey);
        }

        $editId = $this->getFilters()->getEditId();

        if ($editId) {
            if ($template = $this->templateService->getTemplateByPageId($editId)) {
                return $template;
            }
        }

        if ($firstTemplate = $this->templateService->getDefaultTemplate()) {
            return $firstTemplate;
        }

        return null;
    }

    /**
     * @return null|int
     */
    private function getParentId(): ?int
    {
        $pageId = $this->getFilters()->getEditId();

        if ( ! $pageId) {
            return null;
        }

        $page = Page::getById($pageId);

        return (int) $page->parent_id;
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