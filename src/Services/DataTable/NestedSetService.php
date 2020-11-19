<?php declare(strict_types=1);


namespace KikCMS\Services\DataTable;


use KikCMS\Models\Page;
use KikCmsCore\Services\DbService;
use Phalcon\Db\RawValue;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 */
class NestedSetService extends Injectable
{
    /**
     * Set lft, rgt and level for given page, and update existing pages to make room
     *
     * @param Page $page
     */
    public function setAndMakeRoomForNewPage(Page $page)
    {
        if ( ! $page->parent->rgt) {
            $this->addToNestedSet($page->parent);
        }

        $parentRgt = $page->parent->rgt;

        $page->lft = $parentRgt;
        $page->rgt = $parentRgt + 1;

        $page->level = $page->parent->getLevel() + 1;

        $this->db->update(Page::TABLE, [Page::FIELD_RGT], [new RawValue("rgt + 2")], "rgt >= " . $parentRgt);
        $this->db->update(Page::TABLE, [Page::FIELD_LFT], [new RawValue("lft + 2")], "lft >= " . $parentRgt);
    }

    /**
     * @param Page $page
     */
    public function addToNestedSet(Page $page)
    {
        $maxRight = $this->getMaxRightValue();

        $page->lft   = $maxRight + 1;
        $page->rgt   = $maxRight + 2;
        $page->level = 0;

        $page->save();
    }

    /**
     * @return int
     */
    public function getMaxRightValue(): int
    {
        $query = (new Builder)->columns(['MAX(' . Page::FIELD_RGT . ')'])->from(Page::class);
        return (int) $this->dbService->getValue($query);
    }
}