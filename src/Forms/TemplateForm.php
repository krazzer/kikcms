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
            $this->addTextField('name', $this->translator->tlb('name'), [new PresenceOf()]),
            $this->addTextField('file', $this->translator->tlb('file'), [new PresenceOf()]),
            $this->addCheckboxField('hide', $this->translator->tlb('hide')),
            $this->addDataTableField(new TemplateFields(), $this->translator->tlb('template_fields')),
        ]);

        $this->addTab('Alle velden', [
            $this->addDataTableField(new Fields(), $this->translator->tlb('all_fields')),
        ]);
    }
}