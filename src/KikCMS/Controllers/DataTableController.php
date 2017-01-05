<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\DataTable\DataTable;

class DataTableController extends BaseController
{
    public function initialize()
    {
        parent::initialize();

        $this->view->disable();
    }

    public function editAction()
    {
        $editId    = $this->getEditId();
        $dataTable = $this->getDataTable();

        $this->view->form = $dataTable->renderEditForm($editId);

        return json_encode([
            'window' => $this->view->getRender('data-table', 'edit')
        ]);
    }

    public function saveAction()
    {
        $editId    = $this->getEditId();
        $dataTable = $this->getDataTable();

        $this->view->form = $dataTable->renderEditForm($editId);

        return json_encode([
            'table'    => $dataTable->renderTable($this->getFilters()),
            'window'   => $this->view->getRender('data-table', 'edit'),
            'editedId' => $editId,
        ]);
    }

    public function pageAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination($filters[DataTable::FILTER_PAGE]),
        ]);
    }

    public function searchAction()
    {
        $dataTable   = $this->getDataTable();
        $searchValue = $this->request->getPost(DataTable::FILTER_SEARCH);

        return json_encode([
            'table'      => $dataTable->renderTable([DataTable::FILTER_SEARCH => $searchValue]),
            'pagination' => $dataTable->renderPagination(1),
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

    /**
     * @return array
     */
    private function getFilters(): array
    {
        $filters = [];

        $filters[DataTable::FILTER_PAGE] = $this->request->getPost(DataTable::FILTER_PAGE);
        $filters[DataTable::FILTER_SEARCH] = $this->request->getPost(DataTable::FILTER_SEARCH);

        return $filters;
    }
}