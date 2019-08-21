<?php declare(strict_types=1);


namespace KikCMS\Models;


use KikCmsCore\Classes\Model;

class FilePermission extends Model
{
    public const TABLE = 'cms_file_permission';
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