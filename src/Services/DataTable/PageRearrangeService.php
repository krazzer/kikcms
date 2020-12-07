<?php declare(strict_types=1);

namespace KikCMS\Services\DataTable;

use Exception;
use KikCMS\Models\Page;
use KikCMS\Classes\Page\AdjacencyToNestedSet;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\RawValue;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Service for handling Page Model objects
 */
class PageRearrangeService extends Injectable
{
    const REARRANGE_BEFORE = 'before';
    const REARRANGE_AFTER  = 'after';
    const REARRANGE_INTO   = 'into';

    /**
     * Check if there are pages where displayOrder is not set, if so, set them
     */
    public function checkOrderIntegrity()
    {
        if ( ! $this->hasPagesWithoutDisplayOrder()) {
            return;
        }

        $parentPageMap = $this->pageService->getDisplayOrderMissing();

        foreach ($parentPageMap as $parentPage) {
            $children     = $this->pageService->getChildren($parentPage);
            $displayOrder = $this->getMaxDisplayOrder($parentPage) + 1;

            // loop through all children and set a new display_order
            foreach ($children as $id => $page) {
                $this->dbService->update(Page::class, [Page::FIELD_DISPLAY_ORDER => $displayOrder], [Page::FIELD_ID => $id]);
                $displayOrder++;
            }
        }
    }

    /**
     * Checks if the url of the source page is not conflicting in its new target location, if so, change it
     *
     * @param Page $page
     */
    public function checkUrls(Page $page)
    {
        foreach ($page->pageLanguages as $pageLanguage) {
            // if there's no url, we don't need to check for dupes
            if ( ! $pageLanguage->getSlug()) {
                continue;
            }

            $urlPath = $this->urlService->getUrlByPageLanguage($pageLanguage);

            if ($this->urlService->urlPathExists($urlPath, $pageLanguage)) {
                $this->urlService->deduplicateAndStoreNewUrl($pageLanguage);
            }
        }
    }

    /**
     * @param Page|null $parentPage
     * @return int
     */
    public function getMaxDisplayOrder(?Page $parentPage): int
    {
        $query = (new Builder())
            ->from(Page::class)
            ->columns(["MAX(" . Page::FIELD_DISPLAY_ORDER . ")"])
            ->where('parent_id = :parentId:', ['parentId' => $parentPage ? $parentPage->getId() : null]);

        return (int) $this->dbService->getValue($query);
    }

    /**
     * @param Page $page
     * @param Page $targetPage
     * @param string $rearrange
     */
    public function rearrange(Page $page, Page $targetPage, string $rearrange)
    {
        $this->checkOrderIntegrity();

        if ($this->pageService->isOffspringOf($targetPage, $page)) {
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
        $this->cacheService->clearForPage($page);
    }

    /**
     * @param int|null $parentId
     * @param int|null $dislplayOrder
     */
    public function updateLeftSiblingsOrder(int $parentId = null, int $dislplayOrder = null)
    {
        if ( ! $dislplayOrder) {
            return;
        }

        $this->db->update(Page::TABLE, [Page::FIELD_DISPLAY_ORDER], [new RawValue("display_order - 1")], "
            display_order > " . $dislplayOrder . "
            AND parent_id" . ($parentId ? ' = ' . $parentId : ' IS NULL') . "
            ORDER BY display_order ASC 
        ");
    }

    /**
     * Convert parent-child to nested set, and save
     */
    public function updateNestedSet()
    {
        $this->checkOrderIntegrity();

        $relations = $this->getParentChildRelations();

        $converter = new AdjacencyToNestedSet($relations);
        $converter->traverse();

        $nestedSetStructure = $converter->getResult();

        $this->saveStructure($nestedSetStructure);
    }

    /**
     * @return array
     */
    private function getParentChildRelations(): array
    {
        if ($this->db instanceof Mysql) {
            $this->db->query("SET SESSION group_concat_max_len = 99999");
        }

        $allowedNonParentQuery = "(
            p.parent_id IS NULL 
            AND (
                p.type = 'menu' OR 
                EXISTS(SELECT id FROM cms_page WHERE parent_id = p.id))
            )";

        $relations = $this->dbService->queryAssoc("
            SELECT 0, GROUP_CONCAT(p.id ORDER BY p.display_order ASC) 
            FROM cms_page p 
            WHERE " . $allowedNonParentQuery . "
            
            UNION
            
            SELECT p.id, GROUP_CONCAT(c.id ORDER BY c.display_order ASC) 
            FROM cms_page p  
            LEFT JOIN cms_page c ON p.id = c.parent_id
            WHERE p.parent_id IS NOT NULL OR " . $allowedNonParentQuery . " 
            GROUP BY p.id
        ");

        foreach ($relations as $parentId => $childIds) {
            $childIds             = $childIds ? explode(',', $childIds) : [];
            $relations[$parentId] = array_map(function ($id) {
                return (int) $id;
            }, $childIds);
        }

        return $relations;
    }

    /**
     * @return bool
     */
    private function hasPagesWithoutDisplayOrder(): bool
    {
        $query = (new Builder)
            ->from(Page::class)
            ->where(Page::FIELD_DISPLAY_ORDER . ' IS NULL')
            ->andWhere(Page::FIELD_PARENT_ID . ' IS NOT NULL');

        return $this->dbService->getExists($query);
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
        $this->dbService->transaction(function () use ($page, $targetPage, $placeAfter) {
            $targetParentId     = $targetPage->getParentId();
            $targetDisplayOrder = $targetPage->display_order;
            $newDisplayOrder    = $targetDisplayOrder ? $targetDisplayOrder + ($placeAfter ? 1 : 0) : null;

            $oldDisplayOrder = $page->getDisplayOrder();
            $oldParentId     = $page->getParentId();

            $this->updateSiblingOrder($targetPage, $placeAfter);
            $this->updatePage($page, $targetParentId, $newDisplayOrder);
            $this->updateLeftSiblingsOrder($oldParentId, $oldDisplayOrder);
        });
    }

    /**
     * @param Page $page
     * @param Page $targetPage
     */
    private function placeInto(Page $page, Page $targetPage)
    {
        $menu = $this->pageService->getMaxLevelDeterminer($targetPage);

        // can't put page if target exceeds or equals menu's max level
        if ($menu && $targetPage->level >= ($menu->menu_max_level + $menu->level)) {
            return;
        }

        // no use placing a page into it's own parent
        if ($page->parent_id == $targetPage->getId()) {
            return;
        }

        // can't put page into detached page
        if ( ! $targetPage->parent_id && $targetPage->type != Page::TYPE_MENU) {
            return;
        }

        $displayOrder = $this->getMaxDisplayOrder($targetPage) + 1;

        $oldDisplayOrder = $page->getDisplayOrder();
        $oldParentId     = $page->getParentId();

        $this->updatePage($page, (int) $targetPage->id, $displayOrder);
        $this->updateLeftSiblingsOrder($oldParentId, $oldDisplayOrder);
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

        if ( ! $insertValues) {
            return;
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
     * @param int|null $parentId
     * @param int|null $displayOrder
     */
    private function updatePage(Page $page, int $parentId = null, int $displayOrder = null)
    {
        $page->setParentId($parentId)->setDisplayOrder($displayOrder)->save();
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