<?php

namespace KikCMS\Controllers;


use KikCMS\Models\Page;
use KikCMS\Services\DataTable\PageRearrangeService;

/**
 * @property PageRearrangeService $pageRearrangeService
 */
class PagesDataTableController extends DataTableController
{
    public function treeOrderAction()
    {
        $pageId       = $this->request->getPost('pageId');
        $targetPageId = $this->request->getPost('targetPageId');
        $rearrange    = $this->request->getPost('position');

        $page       = Page::getById($pageId);
        $targetPage = Page::getById($targetPageId);

        $this->pageRearrangeService->rearrange($page, $targetPage, $rearrange);

        $dataTable = $this->getDataTable();

        return json_encode(['table' => $dataTable->renderTable()]);
    }
}