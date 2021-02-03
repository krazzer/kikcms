<?php declare(strict_types=1);

namespace KikCMS\Controllers;


use KikCMS\Services\DataTable\DataTableFilterService;
use KikCMS\Services\DataTable\DataTableService;
use KikCMS\Services\DataTable\RearrangeService;
use KikCMS\Services\ModelService;
use KikCMS\Services\Util\QueryService;
use KikCmsCore\Exceptions\DbForeignKeyDeleteException;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Renderable\Renderable;
use Monolog\Logger;
use Phalcon\Http\ResponseInterface;

/**
 * @property AccessControl $acl
 * @property DataTableService $dataTableService
 * @property DbService $dbService
 * @property Logger $logger
 * @property ModelService $modelService
 * @property QueryService $queryService
 * @property DataTableFilterService $dataTableFilterService
 * @property RearrangeService $rearrangeService
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

        return json_encode([
            'window' => $dataTable->renderWindow($dataTable->renderAddForm())
        ]);
    }

    /**
     * @return ResponseInterface
     */
    public function addImageAction(): ResponseInterface
    {
        $dataTable = $this->getRenderable();

        $dataTable->initializeDatatable();

        $fileIds = (array) $this->request->getPost('fileIds', 'int');

        if ($errors = $this->dataTableService->validateDirectImage($fileIds, $dataTable)) {
            return $this->response->setJsonContent(['errors' => $errors]);
        }

        $editIds = [];

        foreach ($fileIds as $fileId) {
            $fileIds[] = $this->dataTableService->addImageDirectly((int) $fileId, $dataTable);
        }

        return $this->response->setJsonContent([
            'table'      => $dataTable->renderTable(),
            'pagination' => $dataTable->renderPagination(),
            'editedIds'  => $editIds,
        ]);
    }

    /**
     * @return string
     * @throws UnauthorizedException
     */
    public function deleteAction(): string
    {
        $dataTable = $this->getRenderable();

        if ( ! $dataTable->canDelete()) {
            throw new UnauthorizedException;
        }

        $ids = (array) $this->request->getPost('ids');

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

        return json_encode([
            'window' => $dataTable->renderWindow($dataTable->renderEditForm())
        ]);
    }

    /**
     * @return string
     * @throws UnauthorizedException
     */
    public function saveAction()
    {
        $dataTable = $this->getRenderable();
        $editId    = $dataTable->getFilters()->getEditId();

        if ( ! $dataTable->canEdit($editId)) {
            throw new UnauthorizedException;
        }

        $renderedForm = $this->dataTableService->getRenderedForm($dataTable);

        // checks if the form has an edit id áfter rendering
        if ($editId === null && $editId = $dataTable->getForm()->getFilters()->getEditId()) {
            $this->dataTableService->handleNewObject($dataTable);
        }

        return json_encode([
            'window'     => $dataTable->renderWindow($renderedForm),
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

        $this->rearrangeService->checkOrderIntegrity($dataTable->getModel(), $dataTable->getSortableField());

        $source = $model::getById($id);
        $target = $model::getById($targetId);

        $this->rearrangeService->rearrange($source, $target, $rearrange, $dataTable->getSortableField());

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
     * @return ResponseInterface
     */
    public function uploadImagesAction(): ResponseInterface
    {
        $dataTable = $this->getRenderable();

        $uploadedFiles = $this->request->getUploadedFiles();
        $uploadStatus  = $this->fileService->uploadFiles($uploadedFiles);

        if ($errors = $uploadStatus->getErrors()) {
            return $this->response->setJsonContent(['errors' => $errors]);
        }

        $fileIds = $uploadStatus->getFileIds();
        $editIds = [];

        foreach ($fileIds as $fileId) {
            $editIds[] = $this->dataTableService->addImageDirectly($fileId, $dataTable);
        }

        return $this->response->setJsonContent([
            'table'      => $dataTable->renderTable(),
            'pagination' => $dataTable->renderPagination(),
            'editedIds'  => $editIds,
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