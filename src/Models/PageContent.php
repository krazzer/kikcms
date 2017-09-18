<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class PageContent extends Model
{
    const TABLE = 'cms_page_content';
    const ALIAS = 'pc';

    const FIELD_PAGE_ID = 'page_id';
    const FIELD_FIELD   = 'field';
    const FIELD_VALUE   = 'value';
}