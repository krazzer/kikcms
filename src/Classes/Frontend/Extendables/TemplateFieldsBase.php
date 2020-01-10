<?php declare(strict_types=1);

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;
use KikCMS\Classes\Page\Template;
use KikCMS\Classes\WebForm\Field;

/**
 * Contains methods to add template fields to a Form in a Pages DataTable
 */
class TemplateFieldsBase extends WebsiteExtendable
{
    /**
     * @return Template[]
     */
    public function getTemplates(): array
    {
        return [];
    }

    /**
     * Get an array of fields for the template. Fields can be one of the following objects: (Field, Tab, FieldTransformer)
     *
     * @return Field[] [key => field Object]
     */
    public function getFields(): array
    {
        return [];
    }
}