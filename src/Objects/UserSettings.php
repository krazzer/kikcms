<?php


namespace KikCMS\Objects;


class UserSettings
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

        return serialize($this);
    }

    /**
     * @return bool
     */
    private function isEmpty(): bool
    {
        return ! $this->closedPageIdMap;
    }
}