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
            $this->addTextField('name', 'Naam', [new PresenceOf()]),
            $this->addDataTableField(new TemplateFields(), 'Template velden'),
        ]);

        $this->addTab('Alle velden', [
            $this->addDataTableField(new Fields(), 'Alle velden'),
        ]);
    }
}