<?php

namespace KikCMS\Forms;


use KikCMS\Models\Page;
use KikCMS\Services\LanguageService;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength;

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
        $dlc = ucfirst($this->languageService->getDefaultLanguageCode());

        $this->addTextField("pageLanguage$dlc:name", 'Naam', [new PresenceOf()]);

        $this->addTextField('menu_max_level', 'Maximum level', [new Numericality()]);
        $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_MENU);
        $this->addTextField(Page::FIELD_KEY, 'Key', [
            new Regex(['pattern' => '/^$|^([0-9a-z\-]+)$/']),
            new StringLength(["max" => 32])
        ]);
    }
}