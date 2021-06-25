<?php declare(strict_types=1);


namespace KikCMS\Classes\DataTable;

use DateTime;

/**
 * Contains the ids of a SubDataTable which parent hasn't been saved yet
 */
class SubDataTableNewIdsCache
{
    /** @var array */
    private $ids;

    /** @var string the column with a temporary value */
    private $column;

    /** @var string */
    private $model;

    /** @var DateTime */
    private $date;

    public function __construct()
    {
        $this->date = new DateTime();
    }

    /**
     * @param int $id
     * @return SubDataTableNewIdsCache
     */
    public function addId(int $id): SubDataTableNewIdsCache
    {
        $this->ids[] = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     * @return SubDataTableNewIdsCache
     */
    public function setIds(array $ids): SubDataTableNewIdsCache
    {
        $this->ids = $ids;
        return $this;
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @param string $column
     * @return SubDataTableNewIdsCache
     */
    public function setColumn(string $column): SubDataTableNewIdsCache
    {
        $this->column = $column;
        return $this;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @param string $model
     * @return SubDataTableNewIdsCache
     */
    public function setModel(string $model): SubDataTableNewIdsCache
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $dateTime
     * @return $this
     */
    public function setDate(DateTime $dateTime): SubDataTableNewIdsCache
    {
        $this->date = $dateTime;
        return $this;
    }
}