<?php declare(strict_types=1);


namespace KikCMS\Objects;


use Serializable;

class UserSettings implements Serializable
{
    /** @var array [className => [pageIds]] */
    private $closedPageIdMap = [];

    /**
     * @return array
     */
    public function getClosedPageIdMap(): array
    {
        return $this->closedPageIdMap;
    }

    /**
     * @param array $closedPageIdMap
     * @return UserSettings
     */
    public function setClosedPageIdMap(array $closedPageIdMap): UserSettings
    {
        $this->closedPageIdMap = $closedPageIdMap;
        return $this;
    }

    /**
     * @return null|string
     */
    public function serialize(): ?string
    {
        if($this->isEmpty()){
            return null;
        }

        return serialize($this->closedPageIdMap);
    }

    /**
     * @return bool
     */
    private function isEmpty(): bool
    {
        return ! $this->closedPageIdMap;
    }

    /**
     * @inheritDoc
     */
    public function unserialize($data): UserSettings
    {
        $this->closedPageIdMap = (array) unserialize($data);
        return $this;
    }

    /**
     * @return null[]|string[]
     */
    public function __serialize(): array
    {
        return [$this->serialize()];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->unserialize($data[0]);
    }
}