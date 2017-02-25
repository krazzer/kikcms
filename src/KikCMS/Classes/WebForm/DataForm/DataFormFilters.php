<?php

namespace KikCMS\Classes\WebForm\DataForm;


use KikCMS\Classes\Renderable\Filters;

class DataFormFilters extends Filters
{
    const FILTER_TYPES = [];

    const FILTER_EDIT_ID = null;

    /** @var int|null */
    private $editId;

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
}