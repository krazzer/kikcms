<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\DataForm;


use KikCMS\Classes\Renderable\Filters;

class DataFormFilters extends Filters
{
    /** @var int|null */
    private $editId;

    /** @var int|null */
    private $parentEditId;

    /** @var string|null */
    private $languageCode;

    /**
     * @return null|int
     */
    public function getEditId(): ?int
    {
        return $this->editId;
    }

    /**
     * @param int|null $editId
     * @return DataFormFilters|$this
     */
    public function setEditId(?int $editId): DataFormFilters|static
    {
        $this->editId = $editId;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    /**
     * @param string|null $languageCode
     * @return DataFormFilters
     */
    public function setLanguageCode(?string $languageCode): static
    {
        $this->languageCode = $languageCode;
        return $this;
    }

    /**
     * @return null|int
     */
    public function getParentEditId(): ?int
    {
        return $this->parentEditId;
    }

    /**
     * @param int|null $parentEditId
     * @return DataFormFilters
     */
    public function setParentEditId(?int $parentEditId): static
    {
        $this->parentEditId = $parentEditId;
        return $this;
    }
}