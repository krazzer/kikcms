<?php declare(strict_types=1);

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

    /** @var int|string The key (can also be the id) of the menu (or a page) which children should be shown */
    private $menuKey;

    /** @var int|null Maximum amount of levels to be shown */
    private $maxLevel = null;

    /** @var string|null shows the menu item with a specific template, will be rendered by a block @website/menu.twig */
    private $template = null;

    /** @var array */
    private $restrictTemplates = [];

    /** @var string */
    private $ulClass = '';

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
     * @return int|string
     */
    public function getMenuKey(): int|string
    {
        return $this->menuKey;
    }

    /**
     * @param int|string $menuKey
     * @return Menu
     */
    public function setMenuKey(int|string $menuKey): Menu
    {
        $this->menuKey = $menuKey;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxLevel(): ?int
    {
        return $this->maxLevel;
    }

    /**
     * @param int|null $maxLevel
     * @return Menu
     */
    public function setMaxLevel(?int $maxLevel): Menu
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
     * @param string|null $template
     * @return Menu
     */
    public function setTemplate(?string $template): static
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return array
     */
    public function getRestrictTemplates(): array
    {
        return $this->restrictTemplates;
    }

    /**
     * @param array $restrictTemplates
     * @return Menu
     */
    public function setRestrictTemplates(array $restrictTemplates): static
    {
        $this->restrictTemplates = $restrictTemplates;
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

    /**
     * @return string
     */
    public function getUlClass(): string
    {
        return $this->ulClass;
    }

    /**
     * @param string $ulClass
     * @return Menu
     */
    public function setUlClass(string $ulClass): Menu
    {
        $this->ulClass = $ulClass;
        return $this;
    }
}