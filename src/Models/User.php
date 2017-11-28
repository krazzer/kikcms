<?php

namespace KikCMS\Models;

use KikCmsCore\Classes\Model;

/**
 * @property FinderFile $folder
 */
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

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->hasOne(User::FIELD_ID, FinderFile::class, FinderFile::FIELD_USER_ID, ["alias" => "folder"]);
    }
}