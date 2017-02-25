<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\Field;
use KikCMS\Models\Page;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguage;
use KikCMS\Models\Template;
use KikCMS\Services\Model\FieldService;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * @property FieldService $fieldService
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

        $this->addFieldsForCurrentPage();

        $this->addTab('Geavanceerd', [
            $this->addSelectField(Page::FIELD_TEMPLATE_ID, 'Template', Template::findAssoc())
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Page::class;
    }

    private function addFieldsForCurrentPage()
    {
        $editId = $this->filters->getEditId();

        if ( ! $editId) {
            return;
        }

        $page = Page::getById($editId);

        $fields = $this->fieldService->getByPage($page);

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
}