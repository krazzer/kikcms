<?php

namespace KikCMS\Classes\DataTable;

use Exception;
use KikCMS\Classes\DbService;
use KikCMS\Classes\Model\Model;
use KikCMS\Services\Pages\PageService;
use Phalcon\Db\RawValue;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

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
        $this->checkOrderIntegrity($source, $target);

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
     * Check whether the target and source displayOrder values are set, if not do so
     *
     * @param Model $source
     * @param Model $target
     */
    private function checkOrderIntegrity(Model $source, Model $target)
    {
        $orderField = $this->getOrderField();

        if( ! $source->$orderField){
            $source->$orderField = $this->getMax() + 1;
            $source->save();
        }

        if( ! $target->$orderField){
            $target->$orderField = $this->getMax() + 1;
            $target->save();
        }
    }

    /**
     * Get the maximum order value
     *
     * @return int
     */
    private function getMax(): int
    {
        $query = (new Builder())
            ->from($this->dataTable->getModel())
            ->columns(["MAX(" . $this->dataTable->getOrderField() . ")"]);

        return (int) $this->dbService->getValue($query);
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