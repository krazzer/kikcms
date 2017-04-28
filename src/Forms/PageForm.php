<?php

namespace KikCMS\Forms;


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
 */
class PageForm extends DataForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTab('Pagina', [
            $this->addTextField(PageLanguage::FIELD_NAME, $this->translator->tlb('name'), [new PresenceOf()])
                ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true),
            $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_PAGE),
        ]);

        $this->addFieldsForCurrentTemplate();

        $templateField = $this->addSelectField(Page::FIELD_TEMPLATE_ID, $this->translator->tlb('template'), Template::findAssoc());
        $templateField->getElement()->setDefault($this->getTemplateId());

        $urlValidation = [
            new PresenceOf(),
            new Regex(['pattern' => '/^$|^([0-9a-z\-]+)$/', 'message' => $this->translator->tlb('webform.messages.slug')]),
            new StringLength(["max" => 255]),
        ];

        $this->addTab($this->translator->tlb('advanced'), [
            $templateField,

            $this->addTextField(PageLanguage::FIELD_URL, $this->translator->tlb('url'), $urlValidation)
                ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true)
                ->setPlaceholder($this->translator->tlb('dataTables.pages.urlPlaceholder')),

            $this->addCheckboxField(PageLanguage::FIELD_ACTIVE, $this->translator->tlb('active'))
                ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true)
                ->setDefault(1)
        ]);
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

        if($input['type'] !== Page::TYPE_PAGE){
            return $errorContainer;
        }

        if ( ! $url = $input['url']) {
            return $errorContainer;
        }

        $parentId     = $this->getParentId();
        $pageLanguage = $this->getPageLanguage();
        $languageCode = $this->getFilters()->getLanguageCode();

        if ($this->urlService->urlExists($url, $parentId, $languageCode, $pageLanguage)) {
            $errorContainer->addFieldError('url', $this->translator->tlb('dataTables.pages.urlExists'));
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
                $methodName = 'field' . ucfirst($field->variable);
                $templateField = $this->websiteService->callMethod('TemplateFields', $methodName, [$this]);

                if($templateField){
                    return;
                }
            break;
        }

        if ( ! isset($templateField)) {
            return;
        }

        $templateField->setTableField('value');

        $this->tabs[0]->addField($templateField);

        if( ! array_key_exists($templateField->getKey(), $this->fieldStorage)){
            if($field->multilingual){
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
    private function getParentId()
    {
        $pageId = $this->getFilters()->getEditId();

        if ( ! $pageId) {
            return null;
        }

        $page = Page::getById($pageId);

        return (int) $page->parent_id;
    }

    /**
     * @return PageLanguage|null
     */
    private function getPageLanguage()
    {
        $pageId       = $this->getFilters()->getEditId();
        $languageCode = $this->getFilters()->getLanguageCode();

        if ( ! $pageId) {
            return null;
        }

        return $this->pageLanguageService->getByPageId($pageId, $languageCode);
    }
}