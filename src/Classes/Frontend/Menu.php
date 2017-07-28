<?php

namespace KikCMS\Classes\Frontend;


use KikCMS\ObjectLists\FullPageMap;

class Menu
{
    /** @var bool */
    private $cache = true;

    /** @var string */
    private $languageCode;

    /** @var FullPageMap */
    private $fullPageMap;

    /** @var int The Id of the menu (can also be a page) which children should be shown */
    private $menuId;

    /** @var int|null Maximum amount of levels to be shown */
    private $maxLevel = null;

    /** @var string|null shows the menu item with a specific template, will be rendered by a block @website/menu.twig */
    private $template = null;

    /** @var int|null */
    private $restrictTemplateId = null;

    /**
     * @return bool
     */
    public function isCache(): bool
    {
        return $this->cache;
    }

    /**
     * @param bool $cache
     * @return Menu
     */
    public function setCache(bool $cache): Menu
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @return FullPageMap
     */
    public function getFullPageMap(): FullPageMap
    {
        return $this->fullPageMap;
    }

    /**
     * @param FullPageMap $fullPageMap
     * @return Menu
     */
    public function setFullPageMap(FullPageMap $fullPageMap): Menu
    {
        $this->fullPageMap = $fullPageMap;
        return $this;
    }

    /**
     * @return int
     */
    public function getMenuId(): int
    {
        return $this->menuId;
    }

    /**
     * @param int $menuId
     * @return Menu
     */
    public function setMenuId(int $menuId): Menu
    {
        $this->menuId = $menuId;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxLevel(): int
    {
        return $this->maxLevel;
    }

    /**
     * @param int $maxLevel
     * @return Menu
     */
    public function setMaxLevel(int $maxLevel): Menu
    {
        $this->maxLevel = $maxLevel;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param null|string $template
     * @return Menu
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRestrictTemplateId(): ?int
    {
        return $this->restrictTemplateId;
    }

    /**
     * @param int|null $restrictTemplateId
     * @return Menu
     */
    public function setRestrictTemplateId($restrictTemplateId)
    {
        $this->restrictTemplateId = $restrictTemplateId;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * @param string $languageCode
     * @return Menu
     */
    public function setLanguageCode(string $languageCode): Menu
    {
        $this->languageCode = $languageCode;
        return $this;
    }
}