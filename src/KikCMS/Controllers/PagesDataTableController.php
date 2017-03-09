<?php

namespace KikCMS\Controllers;


use KikCMS\Models\Page;

class PagesDataTableController extends DataTableController
{
    public function treeOrderAction()
    {
        //todo: incomplete. But first finish viewing the correct order, so testing this will be easier

        $pageId       = $this->request->getPost('pageId');
        $targetPageId = $this->request->getPost('targetPageId');
        $position     = $this->request->getPost('position');

        $page       = Page::getById($pageId);
        $targetPage = Page::getById($targetPageId);

        switch ($position) {
            case "before":
                $this->db->query("
                    UPDATE cms_page 
                    SET display_order = display_order + 1 
                    WHERE display_order >= " . $targetPage->display_order . "
                    AND " . ($targetPage->level ? ' = ' . $targetPage->level : 'level IS NULL')
                );

                $this->dbService->update(Page::class, [
                    Page::FIELD_DISPLAY_ORDER => $targetPage->display_order,
                    Page::FIELD_LEVEL         => $targetPage->level,
                ], [
                    Page::FIELD_ID => $page->id
                ]);

                $this->db->query("
                    UPDATE cms_page 
                    SET display_order = display_order - 1 
                    WHERE display_order > " . $page->display_order . "
                    AND " . ($targetPage->level ? 'level = ' . $targetPage->level : 'level IS NULL')
                );
            break;
        }

        return json_encode($this->request->getPost());
    }
}