<?php


namespace KikCMS\Objects;


class PlaceholderFileThumbUrl extends PlaceholderFileUrl
{
    /** @var string */
    private $type;

    /**
     * @param array $arguments
     */
    function mapArguments(array $arguments)
    {
        $this
            ->setFileId((int) $arguments[0])
            ->setType($arguments[1])
            ->setPrivate($arguments[2] == 'private');
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return PlaceholderFileThumbUrl
     */
    public function setType(string $type): PlaceholderFileThumbUrl
    {
        $this->type = $type;
        return $this;
    }
}