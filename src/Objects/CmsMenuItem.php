<?php declare(strict_types=1);

namespace KikCMS\Objects;

/**
 * Value Object for a CMS menu item
 */
class CmsMenuItem
{
    /** @var string */
    private string $id;

    /** @var string */
    private string $route;

    /** @var string */
    private string $label;

    /** @var bool */
    private bool $targetBlank = false;

    /** @var int */
    private int $badge = 0;

    /**
     * @param string $id
     * @param string $label
     * @param string $route
     */
    public function __construct(string $id, string $label, string $route)
    {
        $this->id    = $id;
        $this->route = $route;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return CmsMenuItem
     */
    public function setId(string $id): CmsMenuItem
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param string $route
     * @return CmsMenuItem
     */
    public function setRoute(string $route): CmsMenuItem
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return CmsMenuItem
     */
    public function setLabel(string $label): CmsMenuItem
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTargetBlank(): bool
    {
        return $this->targetBlank;
    }

    /**
     * @param bool $targetBlank
     * @return CmsMenuItem
     */
    public function setTargetBlank(bool $targetBlank): CmsMenuItem
    {
        $this->targetBlank = $targetBlank;
        return $this;
    }

    /**
     * @return int
     */
    public function getBadge(): int
    {
        return $this->badge;
    }

    /**
     * @param int $badge
     * @return CmsMenuItem
     */
    public function setBadge(int $badge): CmsMenuItem
    {
        $this->badge = $badge;
        return $this;
    }
}