<?php declare(strict_types=1);

namespace KikCMS\Controllers;


use KikCMS\Classes\DataTable\DataTable;
use KikCmsCore\Exceptions\DbForeignKeyDeleteException;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\DataTables\Pages;
use KikCMS\Models\Page;
use KikCMS\Services\DataTable\PageRearrangeService;

/**
 * @property PageRearrangeService $pageRearrangeService
 * @property Translator $translator
 */
class PagesDataTableController extends DataTableController
{
    /**
     * @inheritdoc
     */
    public function deleteAction(): string
    {
        try {
            return parent::deleteAction();
        } catch (DbForeignKeyDeleteException) {
            $fkDeleteErrorMsg = $this->translator->tl('dataTables.pages.deleteErrorFk');
            return json_encode(['error' => $fkDeleteErrorMsg]);
        }
    }

    /**
     * @inheritDoc
     */
    public function rearrangeAction(): string
    {
        if($this->getRenderable()->getSortableField() === Page::FIELD_DISPLAY_ORDER){
            return $this->treeOrderAction();
        } else {
            return parent::rearrangeAction();
        }
    }

    /**
     * @return string
     */
    public function treeOrderAction(): string
    {
        $pageId       = $this->request->getPost('id');
        $targetPageId = $this->request->getPost('targetId');
        $rearrange    = $this->request->getPost('position');

        $page       = Page::getById($pageId);
        $targetPage = Page::getById($targetPageId);

        if ($page && ($this->acl->allowed(Permission::PAGE_MENU) || $page->type != Page::TYPE_MENU)) {
            $this->pageRearrangeService->rearrange($page, $targetPage, $rearrange);
        }

        return json_encode(['table' => $this->getRenderable()->renderTable()]);
    }

    /**
     * @inheritdoc
     *
     * @return Renderable|DataTable
     */
    protected function getRenderable(): Renderable
    {
        /** @var Pages $dataTable */
        $dataTable = parent::getRenderable();

        if ($pageId = $dataTable->getFilters()->getEditId()) {
            if($page = Page::getById($pageId)) {
                $dataTable->getFilters()->setPageType((string) $page->type);
            }
        }

        return $dataTable;
    }
}