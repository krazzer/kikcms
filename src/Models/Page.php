<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

/**
 * @property Page $parent
 */
class Page extends Model
{
    const TABLE = 'cms_page';
    const ALIAS = 'p';

    const FIELD_ID            = 'id';
    const FIELD_TYPE          = 'type';
    const FIELD_PARENT_ID     = 'parent_id';
    const FIELD_TEMPLATE      = 'template';
    const FIELD_DISPLAY_ORDER = 'display_order';
    const FIELD_KEY           = 'key';
    const FIELD_LEVEL         = 'level';
    const FIELD_LFT           = 'lft';
    const FIELD_RGT           = 'rgt';
    const FIELD_LINK          = 'link';

    const TYPE_PAGE  = 'page';
    const TYPE_MENU  = 'menu';
    const TYPE_LINK  = 'link';
    const TYPE_ALIAS = 'alias';

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->rgt - $this->lft > 1;
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->belongsTo("parent_id", Page::class, "id", ["alias" => "parent"]);
    }

    /**
     * @inheritdoc
     * @return Page
     */
    public static function getById($id)
    {
        return parent::getById($id);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return (int) $this->level;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return (int) $this->parent_id;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return (string) $this->template;
    }
}