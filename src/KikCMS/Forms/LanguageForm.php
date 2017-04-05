<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Models\Language;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

class LanguageForm extends DataForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField(Language::FIELD_NAME, $this->translator->tl('name'), [new PresenceOf()]);
        $this->addTextField(Language::FIELD_CODE, $this->translator->tl('code'), [new PresenceOf(), new StringLength(['max' => 2, 'min' => 2])]);
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Language::class;
    }
}