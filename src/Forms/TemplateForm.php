<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\DataTables\Fields;
use KikCMS\DataTables\TemplateFields;
use KikCMS\Models\Template;
use Phalcon\Validation\Validator\PresenceOf;

class TemplateForm extends DataForm
{
    /**
     * @return string
     */
    public function getModel(): string
    {
        return Template::class;
    }

    /**
     * @inheritdoc
     */
    public function initialize(int $editId = null)
    {
        $this->addTab('Template', [
            $this->addTextField('name', $this->translator->tl('name'), [new PresenceOf()]),
            $this->addTextField('file', $this->translator->tl('file'), [new PresenceOf()]),
            $this->addCheckboxField('hide', $this->translator->tl('hide')),
            $this->addDataTableField(new TemplateFields(), $this->translator->tl('template_fields')),
        ]);

        $this->addTab('Alle velden', [
            $this->addDataTableField(new Fields(), $this->translator->tl('all_fields')),
        ]);
    }
}