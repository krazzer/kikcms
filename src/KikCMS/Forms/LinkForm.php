<?php

namespace KikCMS\Forms;


use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use Phalcon\Validation\Validator\PresenceOf;

class LinkForm extends PageForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField('name', 'Naam', [new PresenceOf()])->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true);
        $this->addAutoCompleteField('link_to', 'Linkt naar')->setSourceModel(PageLanguage::class)->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true);
        $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_LINK);
    }
}