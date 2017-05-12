<?php

namespace KikCMS\Forms;


use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\DataTables\Languages;
use KikCMS\DataTables\Templates;
use KikCMS\DataTables\Translations;

/**
 * @property AccessControl $acl
 */
class SettingsForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        if($this->acl->allowed(Templates::class)){
            $this->addDataTableField(new Templates(), $this->translator->tl("fields.templates"));
        }

        if($this->acl->allowed(Templates::class)) {
            $this->addDataTableField(new Languages(), $this->translator->tl("fields.languages"));
        }

        $this->addDataTableField(new Translations(), $this->translator->tl("fields.translations"));
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return '';
    }
}