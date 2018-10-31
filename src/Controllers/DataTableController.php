<?php

namespace KikCMS\Controllers;


use Exception;
use KikCMS\Services\ModelService;
use KikCmsCore\Exceptions\DbForeignKeyDeleteException;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\Rearranger;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Renderable\Renderable;
use Monolog\Logger;

/**
 * @property AccessControl $acl
 * @property DbService $dbService
 * @property ModelService $modelService
 * @property Logger $logger
 */
class DataTableController extends RenderableController
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
        $dataTable = $this->getRenderable();

        $this->view->form   = $dataTable->renderAddForm();
        $this->view->labels = $dataTable->getLabels();

        return json_encode([
            'window' => $dataTable->renderWindow(self::TEMPLATE_ADD)
        ]);
    }

    /**
     * @return string
     * @throws UnauthorizedException
     */
    public function deleteAction()
    {
        $dataTable = $this->getRenderable();

        if ( ! $dataTable->canDelete()) {
            throw new UnauthorizedException;
        }

        $ids = $this->request->getPost('ids');

        try {
            $dataTable->delete($ids);
        } catch (DbForeignKeyDeleteException $e) {
            return json_encode(['error' => $this->translator->tl('dataTable.deleteErrorLinked')]);
        }

        return json_encode([
            'table'      => $dataTable->renderTable(),
            'pagination' => $dataTable->renderPagination(),
        ]);
    }

    /**
     * @return string
     */
    public function checkCheckboxAction()
    {
        $id      = $this->request->getPost('editId');
        $column  = $this->request->getPost('column');
        $checked = $this->request->getPost('checked');

        $success = $this->getRenderable()->checkCheckbox($id, $column, $checked);

        return json_encode($success);
    }

    /**
     * @return string
     */
    public function editAction()
    {
        $dataTable = $this->getRenderable();

        $this->view->form     = $dataTable->renderEditForm();
        $this->view->labels   = $dataTable->getLabels();
        $this->view->editData = $dataTable->getForm()->getEditData();

        return json_encode([
            'window' => $dataTable->renderWindow(self::TEMPLATE_EDIT)
        ]);
    }

    /**
     * @return string
     * @throws UnauthorizedException
     */
    public function saveAction()
    {
        $dataTable    = $this->getRenderable();
        $editId       = $dataTable->getFilters()->getEditId();
        $parentEditId = $dataTable->getFilters()->getParentEditId();

        if ( ! $dataTable->canEdit($editId)) {
            throw new UnauthorizedException;
        }

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

            // if the datatable has a unsaved parent, cache the new id
            if ($dataTable->getFilters()->getParentRelationKey() && $parentEditId === 0 && $editId) {
                $dataTable->cacheNewId($editId);
            }

            // go to the page where the new id sits
            if ($editId) {
                if ($fromAlias = $dataTable->getQueryFromAlias()) {
                    $column = $fromAlias . '.' . DataTable::TABLE_KEY;
                } else {
                    $column = DataTable::TABLE_KEY;
                }

                $idsQuery = (clone $dataTable->getQuery())->columns([$column]);

                try {
                    $index = array_search($editId, $this->dbService->getValues($idsQuery));
                    $limit = $dataTable->getLimit();
                    $page  = (($index - ($index % $limit)) / $limit) + 1;

                    $dataTable->getFilters()->setPage($page);
                } catch (Exception $exception) {
                    $this->logger->log(Logger::NOTICE, $exception);
                }

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
        $dataTable = $this->getRenderable();

        return json_encode([
            'table'      => $dataTable->renderTable(),
            'pagination' => $dataTable->renderPagination(),
        ]);
    }

    /**
     * @return string
     */
    public function rearrangeAction()
    {
        $dataTable = $this->getRenderable();

        $id        = $this->request->getPost('id');
        $targetId  = $this->request->getPost('targetId');
        $rearrange = $this->request->getPost('position');

        $model = $this->modelService->getModelByClassName($dataTable->getModel());

        $source = $model::getById($id);
        $target = $model::getById($targetId);

        $rearranger = new Rearranger($dataTable);
        $rearranger->rearrange($source, $target, $rearrange);

        return json_encode(['table' => $dataTable->renderTable()]);
    }

    /**
     * @return string
     */
    public function searchAction()
    {
        $dataTable = $this->getRenderable();

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
        $dataTable = $this->getRenderable();

        return json_encode([
            'table'      => $dataTable->renderTable(),
            'pagination' => $dataTable->renderPagination(),
        ]);
    }

    /**
     * @inheritdoc
     * @return Renderable|DataTable
     */
    protected function getRenderable(): Renderable
    {
        if ( ! $this->acl->dataTableAllowed($this->getClass())) {
            throw new UnauthorizedException();
        }

        return parent::getRenderable();
    }
}