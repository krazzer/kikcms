<?php

namespace KikCMS\Classes\WebForm\DataForm;


use KikCMS\Classes\Renderable\Filters;

class DataFormFilters extends Filters
{
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