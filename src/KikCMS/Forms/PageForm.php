<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\Field;
use KikCMS\Models\Page;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguage;
use KikCMS\Models\Template;
use KikCMS\Services\Model\TemplateService;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * @property TemplateService $templateService
 */
class PageForm extends DataForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTab('Pagina', [
            $this->addTextField('name', 'Naam', [new PresenceOf()])
                ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true),
            $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_PAGE)
        ]);

        $this->addFieldsForCurrentTemplate();

        $templateField = $this->addSelectField(Page::FIELD_TEMPLATE_ID, 'Template', Template::findAssoc());
        $templateField->getElement()->setDefault($this->getTemplateId());

        $this->addTab('Geavanceerd', [
            $templateField
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

        $editData[Page::FIELD_TEMPLATE_ID] = $this->getTemplateId();

        return $editData;
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Page::class;
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
        switch ($field->type_id) {
            case KikCMSConfig::CONTENT_TYPE_TEXT:
                $templateField = $this->addTextField('value', $field->name);
            break;

            case KikCMSConfig::CONTENT_TYPE_TEXTAREA:
                $templateField = $this->addTextAreaField('value', $field->name);
            break;

            case KikCMSConfig::CONTENT_TYPE_TINYMCE:
                $templateField = $this->addWysiwygField('value', $field->name);
            break;
        }

        if ( ! isset($templateField)) {
            return;
        }

        $this->tabs[0]->addField($templateField);

        $templateField->table(PageContent::class, PageContent::FIELD_PAGE_ID, true, [
            PageContent::FIELD_FIELD_ID => $field->id
        ]);
    }

    /**
     * @return int
     */
    private function getTemplateId(): int
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
}