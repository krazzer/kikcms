<?php declare(strict_types=1);

namespace KikCMS\Classes\Finder;


class UploadStatus
{
    /** @var string[] */
    private $errors = [];

    /** @var int[] ids of succesfully created FinderIds */
    private $fileIds = [];

    /**
     * @param string $message
     */
    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * @param int $fileId
     */
    public function addFileId(int $fileId): void
    {
        $this->fileIds[] = $fileId;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return int[]
     */
    public function getFileIds(): array
    {
        return $this->fileIds;
    }
}