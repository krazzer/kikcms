<?php

namespace KikCMS\Services\Model;

use Exception;
use KikCMS\Classes\DbService;
use KikCMS\Models\Page;
use KikCMS\Util\AdjacencyToNestedSet;
use Phalcon\Db\RawValue;
use Phalcon\Di\Injectable;

/**
 * Service for handling Page Model objects
 *
 * @property DbService dbService
 * @property PageService pageService
 */
class PageRearrangeService extends Injectable
{
    const REARRANGE_BEFORE = 'before';
    const REARRANGE_AFTER  = 'after';
    const REARRANGE_INTO   = 'into';

    /**
     * @param Page $page
     * @param Page $targetPage
     * @param string $rearrange
     */
    public function rearrange(Page $page, Page $targetPage, string $rearrange)
    {
        if ($this->pageService->isChildOf($targetPage, $page)) {
            return;
        }

        switch ($rearrange) {
            case self::REARRANGE_BEFORE:
                $this->placeBeforeOrAfter($page, $targetPage, false);
            break;
            case self::REARRANGE_AFTER:
                $this->placeBeforeOrAfter($page, $targetPage, true);
            break;
            case self::REARRANGE_INTO:
                $this->placeInto($page, $targetPage);
            break;
        }

        $this->updateNestedSet();
    }

    /**
     * @return array
     */
    private function getParentChildRelations(): array
    {
        $relations = $this->dbService->queryAssoc("
            SELECT 0, GROUP_CONCAT(p.id ORDER BY p.display_order ASC) 
            FROM cms_page p 
            WHERE p.type = 'menu' 
            
            UNION
            
            SELECT p.id, GROUP_CONCAT(c.id ORDER BY c.display_order ASC) 
            FROM cms_page p  
            LEFT JOIN cms_page c ON p.id = c.parent_id
            WHERE ((p.parent_id IS NOT NULL) OR (p.parent_id IS NULL AND p.type = 'menu')) 
            GROUP BY p.id
        ");

        foreach ($relations as $parentId => $childIds) {
            $relations[$parentId] = $childIds ? explode(',', $childIds) : [];
        }

        return $relations;
    }

    /**
     * @param Page $page
     * @param Page $targetPage
     * @param bool $placeAfter
     *
     * @throws Exception
     */
    private function placeBeforeOrAfter(Page $page, Page $targetPage, bool $placeAfter)
    {
        $targetParentId     = $targetPage->parent_id;
        $targetDisplayOrder = $targetPage->display_order;
        $newDisplayOrder    = $targetDisplayOrder ? $targetDisplayOrder + ($placeAfter ? 1 : 0) : null;

        $this->db->begin();

        try {
            $this->updateSiblingOrder($targetPage, $placeAfter);
            $this->updatePage($page, $targetParentId, $newDisplayOrder);
            $this->updateLeftSiblingsOrder($page);
        } catch (Exception $exception) {
            $this->db->rollback();
            throw $exception;
        }

        $this->db->commit();
    }

    /**
     * @param Page $page
     * @param Page $targetPage
     */
    private function placeInto(Page $page, Page $targetPage)
    {
        if ($page->parent_id == $targetPage->id) {
            return;
        }

        if ( ! $targetPage->parent_id && $targetPage->type != Page::TYPE_MENU) {
            return;
        }

        $displayOrder = $this->pageService->getHighestDisplayOrderChild($targetPage) + 1;

        $this->updatePage($page, (int) $targetPage->id, $displayOrder);
        $this->updateLeftSiblingsOrder($page);
    }

    /**
     * Saves the given structure in the db
     *
     * @param array $nestedSetStructure [pageId => [lft, rgt, level]]
     */
    private function saveStructure(array $nestedSetStructure)
    {
        $insertValues = [];

        foreach ($nestedSetStructure as $pageId => $structure) {
            $insertValues[] = '(' . implode(',', array_merge([$pageId], $structure)) . ')';
        }

        $updateQuery = "
            INSERT INTO cms_page (id, lft, rgt, level)
            VALUES " . implode(',', $insertValues) . "
                
            ON DUPLICATE KEY UPDATE 
                lft = values(lft), 
                rgt = values(rgt), 
                level = values(level)
        ";

        $this->dbService->update(Page::class, [
            Page::FIELD_LFT   => null,
            Page::FIELD_RGT   => null,
            Page::FIELD_LEVEL => null
        ]);

        $this->db->query($updateQuery);
    }

    /**
     * @param Page $page
     */
    private function updateLeftSiblingsOrder(Page $page)
    {
        if ( ! $page->display_order) {
            return;
        }

        $this->db->update(Page::TABLE, [Page::FIELD_DISPLAY_ORDER], [new RawValue("display_order - 1")], "
            display_order > " . $page->display_order . "
            AND parent_id" . ($page->parent_id ? ' = ' . $page->parent_id : ' IS NULL') . "
            ORDER BY display_order ASC 
        ");
    }

    /**
     * Convert parent-child to nested set
     */
    private function updateNestedSet()
    {
        $relations = $this->getParentChildRelations();

        $converter = new AdjacencyToNestedSet($relations);
        $converter->traverse();

        $nestedSetStructure = $converter->getResult();

        $this->saveStructure($nestedSetStructure);
    }

    /**
     * @param Page $page
     * @param int|null $parentId
     * @param int|null $displayOrder
     */
    private function updatePage(Page $page, int $parentId = null, int $displayOrder = null)
    {
        $this->dbService->update(Page::class, [
            Page::FIELD_DISPLAY_ORDER => $displayOrder,
            Page::FIELD_PARENT_ID     => $parentId,
        ], [
            Page::FIELD_ID => $page->id
        ]);
    }

    /**
     * @param Page $page
     * @param bool $placeAfter
     */
    private function updateSiblingOrder(Page $page, bool $placeAfter)
    {
        if ( ! $page->display_order) {
            return;
        }

        $this->db->update(Page::TABLE, [Page::FIELD_DISPLAY_ORDER], [new RawValue("display_order + 1")], "
            display_order >= " . ($page->display_order + ($placeAfter ? 1 : 0)) . "
            AND parent_id" . ($page->parent_id ? ' = ' . $page->parent_id : ' IS NULL') . "
            ORDER BY display_order DESC
        ");
    }
}