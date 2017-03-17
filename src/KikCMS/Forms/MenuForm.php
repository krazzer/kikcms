<?php

namespace KikCMS\Forms;


use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;

class MenuForm extends PageForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField('name', 'Naam', [new PresenceOf()])->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true);
        $this->addTextField('menu_max_level', 'Maximum level', [new Numericality()]);
        $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_MENU);
    }
}