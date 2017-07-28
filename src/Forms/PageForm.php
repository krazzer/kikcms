<?php

namespace KikCMS\Forms;


use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Phalcon\Validator\FileType;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Classes\WebForm\ErrorContainer;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\Field;
use KikCMS\Models\Page;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguageContent;
use KikCMS\Models\PageLanguage;
use KikCMS\Models\Template;
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

        $urlValidation = [new PresenceOf(), $urlPatternValidation, new StringLength(["max" => 255]),];

        $templateField = $this->addSelectField(Page::FIELD_TEMPLATE_ID, $this->translator->tl('fields.template'), Template::findAssoc());
        $templateField->getElement()->setDefault($this->getTemplateId());

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
     * Overwrite to make sure the template_id is set when changed
     *
     * @inheritdoc
     */
    public function getEditData(): array
    {
        $editData = parent::getEditData();
        $pageId   = $this->getFilters()->getEditId();

        $defaultLangPage     = $this->pageLanguageService->getByPageId($pageId);
        $defaultLangPageName = $defaultLangPage ? $defaultLangPage->name : '';

        $editData[Page::FIELD_TEMPLATE_ID] = $this->getTemplateId();

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

    private function addFieldsForCurrentTemplate()
    {
        $templateId = $this->getTemplateId();
        $fields     = $this->templateService->getFieldsByTemplateId($templateId);

        /** @var Field $field */
        foreach ($fields as $field) {
            $this->addTemplateField($field);
        }
    }

    /**
     * @param Field $field
     */
    private function addTemplateField(Field $field)
    {
        $fieldKey = 'pageContent' . $field->id;

        switch ($field->type_id) {
            case KikCMSConfig::CONTENT_TYPE_TEXT:
                $templateField = $this->addTextField($fieldKey, $field->name);
            break;

            case KikCMSConfig::CONTENT_TYPE_TEXTAREA:
                $templateField = $this->addTextAreaField($fieldKey, $field->name);
            break;

            case KikCMSConfig::CONTENT_TYPE_TINYMCE:
                $templateField = $this->addWysiwygField($fieldKey, $field->name);
            break;

            case KikCMSConfig::CONTENT_TYPE_IMAGE:
                $imagesOnly    = new FileType([FileType::OPTION_FILETYPES => ['jpg', 'jpeg', 'png', 'gif']]);
                $templateField = $this->addFileField($fieldKey, $field->name, [$imagesOnly]);
            break;

            case KikCMSConfig::CONTENT_TYPE_CUSTOM:
                $templateField = $this->templateFields->getFormField($field->variable, $this);
            break;
        }

        if ( ! isset($templateField) || ! $templateField) {
            return;
        }

        $templateField->setColumn('value');

        $this->tabs[0]->addField($templateField);

        if ( ! $templateField->getStorage()) {
            if ($field->multilingual) {
                $templateField->table(PageLanguageContent::class, PageLanguageContent::FIELD_PAGE_ID, true, [
                    PageLanguageContent::FIELD_FIELD_ID => $field->id
                ]);
            } else {
                $templateField->table(PageContent::class, PageContent::FIELD_PAGE_ID, false, [
                    PageContent::FIELD_FIELD_ID => $field->id
                ]);
            }
        }
    }

    /**
     * @return int
     */
    protected function getTemplateId(): int
    {
        $templateId = $this->request->getPost('templateId');

        if ($templateId) {
            return $templateId;
        }

        $editId = $this->getFilters()->getEditId();

        if ($editId) {
            $template = $this->templateService->getTemplateByPageId($editId);

            if ($template) {
                return (int) $template->id;
            }
        }

        $firstTemplate = $this->templateService->getDefaultTemplate();

        return (int) $firstTemplate->id;
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