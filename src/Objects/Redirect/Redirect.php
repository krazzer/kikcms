<?php declare(strict_types=1);

namespace KikCMS\Objects\Redirect;

use KikCmsCore\Classes\Model;

class Redirect extends Model
{
    public const TABLE = 'cms_redirect';
    public const ALIAS = 'r';

    public const FIELD_ID               = 'id';
    public const FIELD_PATH_FROM        = 'path_from';
    public const FIELD_PATH_TO          = 'path_to';
    public const FIELD_PAGE_LANGUAGE_ID = 'page_language_id';

    /**
     * Initialize relations
     */
    public function initialize()
    {
        parent::initialize();
    }
}
