<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use KikCMS\Models\FinderFile;
use Phalcon\Forms\Element\Hidden;

class FileField extends Field
{
    /** @var bool */
    private $uploadOnly = false;

    /** @var int|null */
    private $folderId;

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $validators = [])
    {
        $element = (new Hidden($key))
            ->setLabel($label)
            ->addValidators($validators)
            ->setAttribute('class', 'fileId');

        $this->element = $element;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_FILE;
    }

    /**
     * @param int $fileId
     *
     * @return null|FinderFile
     */
    public function getFinderFileById(int $fileId): ?FinderFile
    {
        return FinderFile::getById($fileId);
    }

    /**
     * @return int|null
     */
    public function getFolderId()
    {
        return $this->folderId;
    }

    /**
     * @return bool
     */
    public function isUploadOnly(): bool
    {
        return $this->uploadOnly;
    }

    /**
     * @param bool $uploadOnly
     * @return FileField
     */
    public function setUploadOnly(bool $uploadOnly = true): FileField
    {
        $this->uploadOnly = $uploadOnly;
        return $this;
    }

    /**
     * @param int|null $folderId
     * @return FileField
     */
    public function setFolderId(?int $folderId): FileField
    {
        $this->folderId = $folderId;
        return $this;
    }
}