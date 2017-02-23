<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class PageLanguage extends Model
{
    const TABLE = 'cms_page_language';
    const ALIAS = 'pl';

    const FIELD_PAGE_ID         = 'page_id';
    const FIELD_LANGUAGE_CODE   = 'language_code';
    const FIELD_ACTIVE          = 'active';
    const FIELD_NAME            = 'name';
    const FIELD_SEO_TITLE       = 'seo_title';
    const FIELD_SEO_DESCRIPTION = 'seo_description';
    const FIELD_SEO_KEYWORDS    = 'seo_keywords';
}