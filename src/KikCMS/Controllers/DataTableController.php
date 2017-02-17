<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\Filters;
use KikCMS\Classes\DbService;
use KikCMS\Classes\Exceptions\SessionExpiredException;

/**
 * @property DbService dbService
 */
class DataTableController extends BaseController
{
    const TEMPLATE_ADD  = 'add';
    const TEMPLATE_EDIT = 'edit';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->view->disable();
    }

    /**
     * @return string
     */
    public function addAction()
    {
        $dataTable = $this->getDataTable();

        $this->view->form   = $dataTable->renderAddForm($this->getFilters()->getParentEditId());
        $this->view->labels = $dataTable->getLabels();

        return json_encode([
            'window' => $dataTable->renderWindow(self::TEMPLATE_ADD)
        ]);
    }

    /**
     * @return string
     */
    public function deleteAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        $ids = $this->request->getPost('ids');

        $dataTable->delete($ids);

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination($filters),
        ]);
    }

    /**
     * @return string
     */
    public function editAction()
    {
        $editId    = $this->getEditId();
        $dataTable = $this->getDataTable();

        $this->view->form     = $dataTable->renderEditForm($editId);
        $this->view->labels   = $dataTable->getLabels();
        $this->view->editData = $dataTable->getForm()->getEditData($editId);

        return json_encode([
            'window' => $dataTable->renderWindow(self::TEMPLATE_EDIT)
        ]);
    }

    /**
     * @return string
     */
    public function saveAction()
    {
        $editId    = $this->getEditId();
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        $parentEditId = $filters->getParentEditId();

        if ($editId === null) {
            $this->view->form = $dataTable->renderAddForm($parentEditId);
            $view             = self::TEMPLATE_ADD;

            // if the form was succesfully saved, an edit id can be fetched
            $editId = $dataTable->getEditId();

            // if the datatable has a unsaved parent, cache the new id
            if ($dataTable->hasParent() && $parentEditId === 0 && $editId) {
                $dataTable->cacheNewId($editId);
            }
        } else {
            $this->view->form     = $dataTable->renderEditForm($editId);
            $this->view->editData = $dataTable->getForm()->getEditData($editId);
            $view                 = self::TEMPLATE_EDIT;
        }

        $this->view->labels = $dataTable->getLabels();

        return json_encode([
            'window'     => $dataTable->renderWindow($view),
            'table'      => $dataTable->renderTable($this->getFilters()),
            'pagination' => $dataTable->renderPagination($this->getFilters()),
            'editedId'   => $editId,
        ]);
    }

    /**
     * @return string
     */
    public function pageAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination($filters),
        ]);
    }

    /**
     * @return string
     */
    public function searchAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        $filters->setPage(1);

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination($filters),
        ]);
    }

    /**
     * @return string
     */
    public function sortAction()
    {
        $dataTable = $this->getDataTable();
        $filters   = $this->getFilters();

        $filters->setPage(1);

        return json_encode([
            'table'      => $dataTable->renderTable($filters),
            'pagination' => $dataTable->renderPagination($filters),
        ]);
    }

    /**
     * @return DataTable
     * @throws SessionExpiredException
     */
    private function getDataTable()
    {
        $instanceName = $this->request->getPost(DataTable::INSTANCE);

        if ( ! $this->session->has(DataTable::SESSION_KEY) ||
            ! array_key_exists($instanceName, $this->session->get(DataTable::SESSION_KEY))
        ) {
            throw new SessionExpiredException();
        }

        $instanceClass = $this->session->get(DataTable::SESSION_KEY)[$instanceName]['class'];

        /** @var DataTable $dataTable */
        $dataTable = new $instanceClass();
        $dataTable->setInstanceName($instanceName);

        return $dataTable;
    }

    /**
     * @return int|null
     */
    private function getEditId()
    {
        return $this->request->getPost(DataTable::EDIT_ID);
    }

    /**
     * @return Filters
     */
    private function getFilters(): Filters
    {
        $filters = new Filters();
        $filters->setByArray($this->request->getPost());

        return $filters;
    }
}