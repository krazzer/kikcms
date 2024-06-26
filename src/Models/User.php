<?php declare(strict_types=1);

namespace KikCMS\Models;

use KikCMS\Classes\Database\Now;
use KikCMS\Classes\Permission;
use KikCmsCore\Classes\Model;

/**
 * @property Folder $folder
 */
class User extends Model
{
    /** @var int */
    public $id;

    const TABLE = 'cms_user';
    const ALIAS = 'ku';

    const FIELD_ID          = 'id';
    const FIELD_EMAIL       = 'email';
    const FIELD_PASSWORD    = 'password';
    const FIELD_BLOCKED     = 'blocked';
    const FIELD_ROLE        = 'role';
    const FIELD_CREATED_AT  = 'created_at';
    const FIELD_REMEMBER_ME = 'remember_me';
    const FIELD_SETTINGS    = 'settings';

    /** @var string */
    private $remember_me;

    /** @var string */
    private $settings;

    /**
     * @return void
     */
    public function beforeValidation(): void
    {
        if ( ! property_exists($this, self::FIELD_CREATED_AT)) {
            $this->created_at = new Now;
        }
    }

    /**
     * @inheritdoc
     * @return User|null
     */
    public static function getById($id): ?User
    {
        /** @var User $kikCmsUser */
        $kikCmsUser = parent::getById($id);

        return $kikCmsUser;
    }

    /**
     * @inheritdoc
     * @return User|null
     */
    public static function findFirst($parameters = null): ?Model
    {
        /** @var User $user */
        $user = parent::findFirst($parameters);

        return $user;
    }

    /**
     * Initialize relations
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->hasOne(User::FIELD_ID, Folder::class, File::FIELD_USER_ID, ["alias" => "folder"]);

        $this->skipAttributesOnCreate([self::FIELD_CREATED_AT]);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        if ( ! property_exists($this, self::FIELD_ID)) {
            return null;
        }

        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getRememberMe(): ?string
    {
        return $this->remember_me;
    }

    /**
     * @param null|string $rememberMe
     * @return User
     */
    public function setRememberMe(?string $rememberMe): User
    {
        $this->remember_me = $rememberMe;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSettings(): ?string
    {
        return $this->settings;
    }

    /**
     * @param string|null $settings
     * @return User
     */
    public function setSettings(?string $settings): User
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return bool
     */
    public function isClient(): bool
    {
        return ! in_array($this->role, [Permission::ADMIN, Permission::DEVELOPER, Permission::USER]);
    }
}