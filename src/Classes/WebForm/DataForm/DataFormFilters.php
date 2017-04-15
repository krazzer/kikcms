<?php

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
     * @return int|null
     */
    public function getEditId()
    {
        return $this->editId;
    }

    /**
     * @param int|null $editId
     * @return DataFormFilters|$this
     */
    public function setEditId($editId)
    {
        $this->editId = $editId;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * @param string $languageCode
     * @return DataFormFilters
     */
    public function setLanguageCode(string $languageCode)
    {
        $this->languageCode = $languageCode;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getParentEditId()
    {
        return $this->parentEditId;
    }

    /**
     * @param int|null $parentEditId
     * @return DataFormFilters
     */
    public function setParentEditId($parentEditId)
    {
        $this->parentEditId = $parentEditId;
        return $this;
    }
}