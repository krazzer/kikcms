<?php

namespace KikCMS\Forms;


use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\DataTables\Languages;
use KikCMS\DataTables\Translations;

/**
 * @property AccessControl $acl
 */
class SettingsForm extends WebForm
{
    protected $displaySendButton = false;

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        if($this->acl->allowed(Languages::class)) {
            $this->addKeyedDataTableField('languages', Languages::class, $this->translator->tl("fields.languages"));
        }

        $this->addKeyedDataTableField('translations', Translations::class, $this->translator->tl("fields.translations"));
    }
}