<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\Exceptions\DbForeignKeyDeleteException;
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
    public function deleteAction()
    {
        try {
            return parent::deleteAction();
        } catch (DbForeignKeyDeleteException $e) {
            $fkDeleteErrorMsg = $this->translator->tlb('dataTables.pages.deleteErrorFk');
            return json_encode(['error' => $fkDeleteErrorMsg]);
        }
    }

    /**
     * @return string
     */
    public function treeOrderAction()
    {
        $pageId       = $this->request->getPost('pageId');
        $targetPageId = $this->request->getPost('targetPageId');
        $rearrange    = $this->request->getPost('position');

        $page       = Page::getById($pageId);
        $targetPage = Page::getById($targetPageId);

        $this->pageRearrangeService->rearrange($page, $targetPage, $rearrange);

        $dataTable = $this->getRenderable();

        return json_encode(['table' => $dataTable->renderTable()]);
    }

    /**
     * @inheritdoc
     */
    protected function getRenderable(): Renderable
    {
        /** @var Pages $dataTable */
        $dataTable = parent::getRenderable();

        if ($pageId = $dataTable->getFilters()->getEditId()) {
            $page = Page::getById($pageId);
            $dataTable->getFilters()->setPageType($page->type);
        }

        return $dataTable;
    }
}