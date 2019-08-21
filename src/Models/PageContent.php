<?php declare(strict_types=1);

namespace KikCMS\Models;

use KikCmsCore\Classes\Model;

class PageContent extends Model
{
    const TABLE = 'cms_page_content';
    const ALIAS = 'pc';

    const FIELD_PAGE_ID = 'page_id';
    const FIELD_FIELD   = 'field';
    const FIELD_VALUE   = 'value';
}