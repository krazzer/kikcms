<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class KikcmsUser extends Model
{
    /** @var int */
    public $id;

    const TABLE = 'kikcms_user';
    const ALIAS = 'ku';

    const FIELD_ID         = 'id';
    const FIELD_EMAIL      = 'email';
    const FIELD_PASSWORD   = 'password';
    const FIELD_ACTIVE     = 'active';
    const FIELD_CREATED_AT = 'created_at';

    /**
     * @inheritdoc
     * @return KikcmsUser
     */
    public static function getById($id)
    {
        /** @var KikcmsUser $kikCmsUser */
        $kikCmsUser = parent::getById($id);

        return $kikCmsUser;
    }

    /**
     * @inheritdoc
     * @return KikcmsUser
     */
    public static function findFirst($parameters = null)
    {
        /** @var KikcmsUser $user */
        $user = parent::findFirst($parameters);

        return $user;
    }
}