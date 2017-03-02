<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\DataTableFilters;
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

        $this->view->form   = $dataTable->renderAddForm();
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

        $ids = $this->request->getPost('ids');

        $dataTable->delete($ids);

        return json_encode([
            'table'      => $dataTable->renderTable(),
            'pagination' => $dataTable->renderPagination(),
        ]);
    }

    /**
     * @return string
     */
    public function editAction()
    {
        $dataTable = $this->getDataTable();

        $this->view->form     = $dataTable->renderEditForm();
        $this->view->labels   = $dataTable->getLabels();
        $this->view->editData = $dataTable->getForm()->getEditData();

        return json_encode([
            'window' => $dataTable->renderWindow(self::TEMPLATE_EDIT)
        ]);
    }

    /**
     * @return string
     */
    public function saveAction()
    {
        $dataTable = $this->getDataTable();

        $editId       = $dataTable->getFilters()->getEditId();
        $parentEditId = $dataTable->getFilters()->getParentEditId();

        if ($editId === null) {
            $this->view->form = $dataTable->renderAddForm();

            $view = self::TEMPLATE_ADD;

            // if the form was succesfully saved, an edit id can be fetched
            $editId = $dataTable->getForm()->getFilters()->getEditId();

            if ($editId) {
                $this->view->editData = $dataTable->getForm()->getEditData();
                $view                 = self::TEMPLATE_EDIT;
            }

            // if the datatable has a unsaved parent, cache the new id
            if ($dataTable->hasParent() && $parentEditId === 0 && $editId) {
                $dataTable->cacheNewId($editId);
            }
        } else {
            $this->view->form     = $dataTable->renderEditForm();
            $this->view->editData = $dataTable->getForm()->getEditData();
            $view                 = self::TEMPLATE_EDIT;
        }

        $this->view->labels = $dataTable->getLabels();

        return json_encode([
            'window'     => $dataTable->renderWindow($view),
            'table'      => $dataTable->renderTable(),
            'pagination' => $dataTable->renderPagination(),
            'editedId'   => $editId,
        ]);
    }

    /**
     * @return string
     */
    public function pageAction()
    {
        $dataTable = $this->getDataTable();

        return json_encode([
            'table'      => $dataTable->renderTable(),
            'pagination' => $dataTable->renderPagination(),
        ]);
    }

    /**
     * @return string
     */
    public function searchAction()
    {
        $dataTable = $this->getDataTable();

        return json_encode([
            'table'      => $dataTable->renderTable(),
            'pagination' => $dataTable->renderPagination(),
        ]);
    }

    /**
     * @return string
     */
    public function sortAction()
    {
        $dataTable = $this->getDataTable();

        return json_encode([
            'table'      => $dataTable->renderTable(),
            'pagination' => $dataTable->renderPagination(),
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

        $filters = new DataTableFilters();
        $filters->setByArray($this->request->getPost());

        /** @var DataTable $dataTable */
        $dataTable = new $instanceClass($filters);
        $dataTable->setInstanceName($instanceName);

        return $dataTable;
    }
}