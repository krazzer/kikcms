<?php declare(strict_types=1);

namespace KikCMS\Services\DataTable;

use Exception;
use KikCmsCore\Classes\ObjectList;
use KikCmsCore\Services\DbService;
use KikCmsCore\Classes\Model;
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
class RearrangeService extends Injectable
{
    const REARRANGE_BEFORE = 'before';
    const REARRANGE_AFTER  = 'after';

    const SORTABLE_FIELD = 'display_order';

    /**
     * Check whether the target and source displayOrder values are set, if not do so
     *
     * @param $className
     * @param string $orderField
     */
    public function checkOrderIntegrity($className, string $orderField = self::SORTABLE_FIELD)
    {
        $objectListWithoutDisplayOrder = $this->getObjectListWithoutDisplayOrder($className, $orderField);

        if( ! $objectListWithoutDisplayOrder->count()){
            return;
        }

        $newOrderValue = $this->getMax($className, $orderField);

        foreach ($objectListWithoutDisplayOrder as $object){
            $newOrderValue++;
            $object->$orderField = $newOrderValue;
            $object->save();
        }
    }

    /**
     * Get the maximum order value
     *
     * @param string $model
     * @param string $sortableField
     * @return int
     */
    public function getMax(string $model, string $sortableField = self::SORTABLE_FIELD): int
    {
        $query = (new Builder)
            ->from($model)
            ->columns(["MAX(" . $sortableField . ")"]);

        return (int) $this->dbService->getValue($query);
    }

    /**
     * Add 1 to all existing display_order values in the table, so a new record can be added as first
     * @param string $model
     * @param string $orderField
     */
    public function makeRoomForFirst(string $model, string $orderField = self::SORTABLE_FIELD)
    {
        $where = "1 = 1 ORDER BY " . $orderField . " DESC";

        $this->dbService->update($model, [$orderField => new RawValue($orderField . " + 1")], $where);
    }

    /**
     * @param Model $source
     * @param Model $target
     * @param string $rearrange
     * @param string $orderField
     */
    public function rearrange(Model $source, Model $target, string $rearrange, string $orderField = self::SORTABLE_FIELD)
    {
        $this->checkOrderValues($source, $target, $orderField);

        switch ($rearrange) {
            case self::REARRANGE_BEFORE:
                $this->placeBeforeOrAfter($source, $target, false, $orderField);
            break;
            case self::REARRANGE_AFTER:
                $this->placeBeforeOrAfter($source, $target, true, $orderField);
            break;
        }
    }

    /**
     * @param string $className
     * @param string $field
     * @return ObjectList
     */
    private function getObjectListWithoutDisplayOrder(string $className, string $field = self::SORTABLE_FIELD): ObjectList
    {
        $query = (new Builder)
            ->from($className)
            ->where($field . ' IS NULL');

        return $this->dbService->getObjectList($query);
    }

    /**
     * @param Model $item
     * @param string $orderField
     */
    private function updateLeftSiblingsOrder(Model $item, string $orderField = self::SORTABLE_FIELD)
    {
        $this->dbService->update($item->getClassName(), [$orderField => new RawValue($orderField . " - 1")],
            $orderField . " > " . $item->$orderField . " ORDER BY " . $orderField . " ASC");
    }

    /**
     * Check whether the target and source displayOrder values are set, if not do so
     *
     * @param Model $source
     * @param Model $target
     * @param string $orderField
     */
    private function checkOrderValues(Model $source, Model $target, string $orderField = self::SORTABLE_FIELD)
    {
        if ( ! $source->$orderField) {
            $source->$orderField = $this->getMax($source->getClassName(), $orderField) + 1;
            $source->save();
        }

        if ( ! $target->$orderField) {
            $target->$orderField = $this->getMax($source->getClassName(), $orderField) + 1;
            $target->save();
        }
    }

    /**
     * @param Model $source
     * @param Model $target
     * @param bool $after
     *
     * @param string $field
     * @throws Exception
     */
    private function placeBeforeOrAfter(Model $source, Model $target, bool $after, string $field = self::SORTABLE_FIELD)
    {
        $targetDisplayOrder = $target->$field;
        $newDisplayOrder    = $targetDisplayOrder ? $targetDisplayOrder + ($after ? 1 : 0) : null;
        $oldSource          = clone $source;

        $this->db->begin();

        try {
            $this->updateSiblingOrder($target, $after, $field);
            $this->updateItem($source, $newDisplayOrder, $field);
            $this->updateLeftSiblingsOrder($oldSource, $field);
        } catch (Exception $exception) {
            $this->db->rollback();
            throw $exception;
        }

        $this->db->commit();
    }

    /**
     * @param Model $item
     * @param int|null $displayOrder
     * @param string $orderField
     * @throws Exception
     */
    private function updateItem(Model $item, int $displayOrder = null, string $orderField = self::SORTABLE_FIELD)
    {
        $item->$orderField = $displayOrder;
        $item->save();
    }

    /**
     * @param Model $item
     * @param bool $placeAfter
     * @param string $orderField
     */
    private function updateSiblingOrder(Model $item, bool $placeAfter, string $orderField = self::SORTABLE_FIELD)
    {
        $this->dbService->update($item->getClassName(), [$orderField => new RawValue($orderField . " + 1")],
            $orderField . " >= " . ($item->$orderField + ($placeAfter ? 1 : 0)) . " ORDER BY " . $orderField . " DESC");
    }
}