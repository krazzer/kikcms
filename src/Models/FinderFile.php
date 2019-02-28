<?php

namespace KikCMS\Models;

use DateTime;
use KikCmsCore\Classes\Model;

/**
 * @property FinderFolder $folder
 */
class FinderFile extends Model
{
    const TABLE = 'cms_file';
    const ALIAS = 'f';

    const FIELD_ID        = 'id';
    const FIELD_FOLDER_ID = 'folder_id';
    const FIELD_IS_FOLDER = 'is_folder';
    const FIELD_NAME      = 'name';
    const FIELD_USER_ID   = 'user_id';
    const FIELD_KEY       = 'key';
    const FIELD_HASH      = 'hash';

    const IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->hasOne(self::FIELD_FOLDER_ID, FinderFolder::class, FinderFolder::FIELD_ID, ['alias' => 'folder']);
    }

    /**
     * @inheritdoc
     * @return FinderFile
     */
    public static function getById($id)
    {
        return parent::getById($id);
    }

    /**
     * @inheritdoc
     *
     * @return FinderFile[]
     */
    public static function getByIdList(array $ids)
    {
        return parent::getByIdList($ids);
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return new DateTime($this->created);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return int|null
     */
    public function getFolderId(): ?int
    {
        return (int) $this->folder_id ?: null;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getThumbNail()
    {
        return $this->fileStorage;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimetype;
    }

    /**
     * @return bool
     */
    public function isFolder(): bool
    {
        return (bool) $this->is_folder;
    }

    /**
     * @return bool
     */
    public function isImage(): bool
    {
        return in_array($this->getMimeType(), self::IMAGE_TYPES);
    }

    /**
     * Returns the mimetype that should be used when outputting the file
     *
     * @return string
     */
    public function getOutputMimeType(): string
    {
        if ($this->getExtension() == 'svg') {
            return 'image/svg+xml';
        }

        return $this->getMimeType();
    }
}