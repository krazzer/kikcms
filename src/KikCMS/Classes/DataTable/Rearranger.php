<?php

namespace KikCMS\Classes\DataTable;

use Exception;
use KikCMS\Classes\DbService;
use KikCMS\Classes\Model\Model;
use KikCMS\Services\Model\PageService;
use Phalcon\Db\RawValue;
use Phalcon\Di\Injectable;

/**
 * Class for rearranging DataTable rows
 *
 * @property DbService dbService
 * @property PageService pageService
 */
class Rearranger extends Injectable
{
    const REARRANGE_BEFORE = 'before';
    const REARRANGE_AFTER  = 'after';

    /** @var DataTable */
    private $dataTable;

    /**
     * @param DataTable $dataTable
     */
    public function __construct(DataTable $dataTable)
    {
        $this->dataTable = $dataTable;
    }

    /**
     * @return string
     */
    public function getOrderField(): string
    {
        return $this->dataTable->getOrderField();
    }

    /**
     * @param Model $source
     * @param Model $target
     * @param string $rearrange
     */
    public function rearrange(Model $source, Model $target, string $rearrange)
    {
        switch ($rearrange) {
            case self::REARRANGE_BEFORE:
                $this->placeBeforeOrAfter($source, $target, false);
            break;
            case self::REARRANGE_AFTER:
                $this->placeBeforeOrAfter($source, $target, true);
            break;
        }
    }

    /**
     * @param Model $item
     */
    public function updateLeftSiblingsOrder(Model $item)
    {
        $model      = $this->dataTable->getModel();
        $orderField = $this->getOrderField();

        if ( ! $item->$orderField) {
            return;
        }

        $this->dbService->update($model, [$orderField => new RawValue($orderField . " - 1")],
            $orderField . " > " . $item->$orderField . " ORDER BY " . $orderField . " ASC");
    }

    /**
     * @param Model $source
     * @param Model $target
     * @param bool $placeAfter
     *
     * @throws Exception
     */
    private function placeBeforeOrAfter(Model $source, Model $target, bool $placeAfter)
    {
        $orderField = $this->getOrderField();

        $targetDisplayOrder = $target->$orderField;
        $newDisplayOrder    = $targetDisplayOrder ? $targetDisplayOrder + ($placeAfter ? 1 : 0) : null;
        $oldSource          = clone $source;

        $this->db->begin();

        try {
            $this->updateSiblingOrder($target, $placeAfter);
            $this->updateItem($source, $newDisplayOrder);
            $this->updateLeftSiblingsOrder($oldSource);
        } catch (Exception $exception) {
            $this->db->rollback();
            throw $exception;
        }

        $this->db->commit();
    }

    /**
     * @param Model $item
     * @param int|null $displayOrder
     */
    private function updateItem(Model $item, int $displayOrder = null)
    {
        $orderField = $this->getOrderField();

        $item->$orderField = $displayOrder;
        $item->save();
    }

    /**
     * @param Model $item
     * @param bool $placeAfter
     */
    private function updateSiblingOrder(Model $item, bool $placeAfter)
    {
        $orderField = $this->getOrderField();

        if ( ! $item->$orderField) {
            return;
        }

        $this->dbService->update($this->dataTable->getModel(), [$orderField => new RawValue($orderField . " + 1")],
            $orderField . " >= " . ($item->$orderField + ($placeAfter ? 1 : 0)) . " ORDER BY " . $orderField . " DESC");
    }
}