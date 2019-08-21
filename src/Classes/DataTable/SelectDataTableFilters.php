<?php declare(strict_types=1);

namespace KikCMS\Classes\DataTable;


class SelectDataTableFilters extends DataTableFilters
{
    /** @var array */
    private $selectedValues = [];

    /**
     * @return array
     */
    public function getSelectedValues(): array
    {
        return $this->selectedValues;
    }

    /**
     * @param array $selectedValues
     * @return SelectDataTableFilters|$this
     */
    public function setSelectedValues(array $selectedValues): SelectDataTableFilters
    {
        $this->selectedValues = $selectedValues;
        return $this;
    }
}