<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use Phalcon\Validation\Validator\PresenceOf;

class PageForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        $this->addTextField('name', 'Naam', [new PresenceOf()])
            ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true);

        $this->addHiddenField('type', Page::TYPE_PAGE);
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Page::class;
    }
}