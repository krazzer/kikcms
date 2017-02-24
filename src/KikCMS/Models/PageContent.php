<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class PageContent extends Model
{
    const TABLE = 'cms_page_content';
    const ALIAS = 'pc';

    const FIELD_PAGE_ID       = 'page_id';
    const FIELD_LANGUAGE_CODE = 'language_code';
    const FIELD_FIELD_ID      = 'field_id';
    const FIELD_VALUE         = 'value';
}