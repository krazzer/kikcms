<?php declare(strict_types=1);


namespace KikCMS\Objects;


class PlaceholderFileUrl extends Placeholder
{
    /** @var bool */
    private $private;

    /** @var int */
    private $fileId;

    /**
     * @param array $arguments
     */
    function mapArguments(array $arguments)
    {
        $this->setFileId((int) $arguments[0])
            ->setPrivate($arguments[1] == 'private');
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     * @return PlaceholderFileUrl|$this
     */
    public function setPrivate(bool $private): PlaceholderFileUrl
    {
        $this->private = $private;
        return $this;
    }

    /**
     * @return int
     */
    public function getFileId(): int
    {
        return $this->fileId;
    }

    /**
     * @param int $fileId
     * @return PlaceholderFileUrl|$this
     */
    public function setFileId(int $fileId): PlaceholderFileUrl
    {
        $this->fileId = $fileId;
        return $this;
    }
}