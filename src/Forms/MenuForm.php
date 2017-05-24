<?php

namespace KikCMS\Forms;


use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\LanguageService;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * @property LanguageService $languageService
 */
class MenuForm extends PageForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField('name', 'Naam', [new PresenceOf()])->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID,
            false, [PageLanguage::FIELD_LANGUAGE_CODE => $this->languageService->getDefaultLanguageCode()]);

        $this->addTextField('menu_max_level', 'Maximum level', [new Numericality()]);
        $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_MENU);
    }
}