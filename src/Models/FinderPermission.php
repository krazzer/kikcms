<?php


namespace KikCMS\Models;


use KikCmsCore\Classes\Model;

class FinderPermission extends Model
{
    public const TABLE = 'finder_permission';
    public const ALIAS = 'fp';

    const FIELD_ID      = 'id';
    const FIELD_ROLE    = 'role';
    const FIELD_USER_ID = 'user_id';
    const FIELD_FILE_ID = 'file_id';
    const FIELD_RIGHT   = 'right';

    /**
     * @return string|int
     */
    public function getKey()
    {
        return $this->role ?: $this->user_id;
    }
}