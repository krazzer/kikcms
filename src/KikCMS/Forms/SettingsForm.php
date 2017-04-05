<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\DataTables\Languages;
use KikCMS\DataTables\Templates;

class SettingsForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        $this->addDataTableField(new Templates(), $this->translator->tl("templates"));
        $this->addDataTableField(new Languages(), $this->translator->tl("languages"));
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return '';
    }
}