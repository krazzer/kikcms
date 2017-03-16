<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class Page extends Model
{
    const TABLE = 'cms_page';
    const ALIAS = 'p';

    const FIELD_ID            = 'id';
    const FIELD_TYPE          = 'type';
    const FIELD_PARENT_ID     = 'parent_id';
    const FIELD_TEMPLATE_ID   = 'template_id';
    const FIELD_DISPLAY_ORDER = 'display_order';
    const FIELD_LEVEL         = 'level';
    const FIELD_LFT           = 'lft';
    const FIELD_RGT           = 'rgt';

    const TYPE_PAGE  = 'page';
    const TYPE_MENU  = 'menu';
    const TYPE_LINK  = 'link';
    const TYPE_ALIAS = 'alias';

    /**
     * @inheritdoc
     *
     * @return Page
     */
    public static function getById($id)
    {
        return parent::getById($id);
    }
}