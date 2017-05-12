<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class User extends Model
{
    /** @var int */
    public $id;

    const TABLE = 'cms_user';
    const ALIAS = 'ku';

    const FIELD_ID         = 'id';
    const FIELD_EMAIL      = 'email';
    const FIELD_PASSWORD   = 'password';
    const FIELD_BLOCKED    = 'blocked';
    const FIELD_ROLE       = 'role';
    const FIELD_CREATED_AT = 'created_at';

    /**
     * @inheritdoc
     * @return User
     */
    public static function getById($id)
    {
        /** @var User $kikCmsUser */
        $kikCmsUser = parent::getById($id);

        return $kikCmsUser;
    }

    /**
     * @inheritdoc
     * @return User
     */
    public static function findFirst($parameters = null)
    {
        /** @var User $user */
        $user = parent::findFirst($parameters);

        return $user;
    }
}