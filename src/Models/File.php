<?php declare(strict_types=1);

namespace KikCMS\Models;

use DateTime;
use KikCMS\Classes\Database\Now;
use KikCmsCore\Classes\Model;
use Phalcon\Mvc\Model\Resultset;

/**
 * @property Folder $folder
 */
class File extends Model
{
    const TABLE = 'cms_file';
    const ALIAS = 'f';

    const FIELD_ID        = 'id';
    const FIELD_NAME      = 'name';
    const FIELD_EXTENSION = 'extension';
    const FIELD_MIMETYPE  = 'mimetype';
    const FIELD_CREATED   = 'created';
    const FIELD_UPDATED   = 'updated';
    const FIELD_IS_FOLDER = 'is_folder';
    const FIELD_FOLDER_ID = 'folder_id';
    const FIELD_SIZE      = 'size';
    const FIELD_USER_ID   = 'user_id';
    const FIELD_KEY       = 'key';
    const FIELD_HASH      = 'hash';

    const IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

    /** @var string|null */
    public $key;

    /**
     * Initialize relations
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->hasOne(self::FIELD_FOLDER_ID, Folder::class, self::FIELD_ID, ['alias' => 'folder']);
    }

    /**
     * @inheritdoc
     * @return File|null
     */
    public static function getById($id): ?File
    {
        return parent::getById($id);
    }

    /**
     * @inheritdoc
     *
     * @return File[]
     */
    public static function getByIdList(array $ids): array|Resultset
    {
        return parent::getByIdList($ids);
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        $created = $this->created ?: 'now';

        if($created instanceof Now){
            $created = 'now';
        }

        return new DateTime($created);
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Returns the real file name of the file on disk.
     *
     * @param bool $private
     * @param string|null $extension
     * @return string
     */
    public function getFileName(bool $private = false, string $extension = null): string
    {
        $name = $private ? $this->getHash() : $this->getId();

        return $name . '.' . ($extension ?: $this->getExtension());
    }

    /**
     * @return int|null
     */
    public function getFolderId(): ?int
    {
        return (int) $this->folder_id ?: null;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return (string) $this->hash;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getThumbNail(): string
    {
        return $this->fileStorage;
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
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

    /**
     * @return DateTime
     */
    public function getUpdated(): DateTime
    {
        $updated = $this->updated ?: 'now';

        if($updated instanceof Now){
            $updated = 'now';
        }

        return new DateTime($updated);
    }

    /**
     * Return how much seconds there are between creation time and updated time
     *
     * @return int
     */
    public function secondsUpdated(): int
    {
        return $this->getUpdated()->getTimestamp() - $this->getCreated()->getTimestamp();
    }

    /**
     * @param string|null $key
     * @return File
     */
    public function setKey(?string $key): File
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param string|null $name
     * @return File
     */
    public function setName(?string $name): File
    {
        $this->name = $name;
        return $this;
    }
}