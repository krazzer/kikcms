<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\Datatable\DataTable;

class DataTableController extends BaseController
{
    public function editAction()
    {
        $editId    = $this->getEditId();
        $datatable = $this->getDatatable();

        $this->view->form = $datatable->renderEditForm($editId);
    }

    public function saveAction()
    {
        $editId    = $this->getEditId();
        $datatable = $this->getDatatable();

        $this->view->form = $datatable->renderEditForm($editId);
        $this->view->pick('datatable/edit');
    }

    /**
     * @return DataTable
     */
    private function getDatatable()
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