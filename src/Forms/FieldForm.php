<?php

namespace KikCMS\Forms;


use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Models\Field;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * @property Translator $translator
 */
class FieldForm extends DataForm
{
    /**
     * @return string
     */
    public function getModel(): string
    {
        return Field::class;
    }

    public function initialize()
    {
        $this->addTextField('name', $this->translator->tl('fields.name'), [new PresenceOf()]);
        $this->addTextField('variable', $this->translator->tl('fields.variable'), [new PresenceOf()]);
        $this->addSelectField('type_id', $this->translator->tl('fields.type'), $this->translator->getContentTypeMap(), [new PresenceOf()]);
        $this->addCheckboxField('multilingual', $this->translator->tl('fields.multilingual'));
    }
}