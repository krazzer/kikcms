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

    public function deleteAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        $ids = $this->request->getPost('ids');

        $dataTable->delete($ids);

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination($filters[DataTable::FILTER_PAGE]),
        ]);
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
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        $filters[DataTable::FILTER_PAGE] = 1;

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination(1),
        ]);
    }

    public function sortAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        $filters[DataTable::FILTER_PAGE] = 1;

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
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

        // get page filter
        $filters[DataTable::FILTER_PAGE] = $this->request->getPost(DataTable::FILTER_PAGE);

        // get search filter
        $search = $this->request->getPost(DataTable::FILTER_SEARCH);

        if ( ! empty($search)) {
            $filters[DataTable::FILTER_SEARCH] = $search;
        }

        // get sort filter
        if ($this->request->hasPost(DataTable::FILTER_SORT_COLUMN)) {
            $filters[DataTable::FILTER_SORT_COLUMN]    = $this->request->getPost(DataTable::FILTER_SORT_COLUMN);
            $filters[DataTable::FILTER_SORT_DIRECTION] = $this->request->getPost(DataTable::FILTER_SORT_DIRECTION);
        }

        return $filters;
    }
}