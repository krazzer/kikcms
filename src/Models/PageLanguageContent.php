<?php declare(strict_types=1);

namespace KikCMS\Models;

use KikCmsCore\Classes\Model;

class PageLanguageContent extends Model
{
    const TABLE = 'cms_page_language_content';
    const ALIAS = 'plc';

    const FIELD_PAGE_ID       = 'page_id';
    const FIELD_LANGUAGE_CODE = 'language_code';
    const FIELD_FIELD         = 'field';
    const FIELD_VALUE         = 'value';
}