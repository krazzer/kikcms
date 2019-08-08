<?php

namespace Website\Classes;


use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCMS\Classes\Page\Template;
use KikCMS\Classes\WebForm\Fields\WysiwygField;
use Phalcon\Validation\Validator\PresenceOf;

class TemplateFields extends TemplateFieldsBase
{
    /**
     * @inheritdoc
     */
    public function getTemplates(): array
    {
        return [
            (new Template('default', 'Standaard', ['content'])),
            (new Template('home', 'Home')),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFields(): array
    {
        return [
            'content' => (new WysiwygField('content*:value', 'Inhoud pagina', [new PresenceOf])),
        ];
    }
}