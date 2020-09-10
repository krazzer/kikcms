<?php declare(strict_types=1);

namespace KikCMS\Classes\Page;


class Template
{
    /** @var string */
    private $key;

    /** @var string */
    private $name;

    /** @var array */
    private $fields;

    /** @var bool */
    private $hidden = false;

    /** @var string */
    private $form;

    /**
     * @var string|null
     *
     * If this is set, this template can only be used for a page with the given key
     * If a page uses this template and key, it will not appear as a choice anymore when adding pages
     */
    private $pageKey;

    /**
     * @param string $key
     * @param string $name
     * @param array $fields
     */
    public function __construct(string $key, string $name, array $fields = [])
    {
        $this->key    = $key;
        $this->name   = $name;
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Template
     */
    public function setKey(string $key): Template
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Template
     */
    public function setName(string $name): Template
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     * @return Template
     */
    public function setFields(array $fields): Template
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     * @return Template
     */
    public function setHidden(bool $hidden = true): Template
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getForm(): ?string
    {
        return $this->form;
    }

    /**
     * @param string $form
     * @return Template
     */
    public function setForm(string $form): Template
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPageKey(): ?string
    {
        return $this->pageKey;
    }

    /**
     * @param string|null $pageKey
     * @return Template
     */
    public function setPageKey(?string $pageKey): Template
    {
        $this->pageKey = $pageKey;
        return $this;
    }
}