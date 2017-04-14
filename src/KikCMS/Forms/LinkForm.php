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
        $this->addTextField(PageLanguage::FIELD_NAME, $this->translator->tl('name'), [new PresenceOf()])
            ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true);

        $this->addTextField(Page::FIELD_LINK, $this->translator->tl('dataTables.pages.linkToDesc'));
        $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_LINK);
    }
}