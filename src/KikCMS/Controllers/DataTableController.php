<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\DataTable\DataTable;

class DataTableController extends BaseController
{
    public function editAction()
    {
        $editId    = $this->getEditId();
        $dataTable = $this->getDataTable();

        $this->view->form = $dataTable->renderEditForm($editId);
    }

    public function saveAction()
    {
        $editId    = $this->getEditId();
        $dataTable = $this->getDataTable();
        $page      = $this->request->getPost(DataTable::PAGE);

        $this->view->form = $dataTable->renderEditForm($editId);

        return json_encode([
            'table'    => $dataTable->renderTable($page),
            'window'   => $this->view->getRender('data-table', 'edit'),
            'editedId' => $editId,
        ]);
    }

    public function pageAction()
    {
        $dataTable = $this->getDataTable();
        $page      = $this->request->getPost('page');

        $this->view->disable();

        return json_encode([
            'table'      => $dataTable->renderTable($page),
            'pagination' => $dataTable->renderPagination($page),
        ]);
    }

    /**
     * @return DataTable
     */
    private function getDataTable()
    {
        $instanceName  = $this->request->getPost(DataTable::INSTANCE);
        $instanceClass = $this->session->get(DataTable::SESSION_KEY)[$instanceName]['class'];

        return new $instanceClass();
    }

    /**
     * @return int
     */
    private function getEditId(): int
    {
        return (int) $this->request->getPost(DataTable::EDIT_ID);
    }
}