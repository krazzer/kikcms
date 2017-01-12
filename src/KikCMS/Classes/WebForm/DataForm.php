<?php

namespace KikCMS\Classes\WebForm;

use Exception;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DbService;
use KikCMS\Classes\Phalcon\FormElements\MultiCheckbox;
use KikCMS\Config\StatusCodes;
use Monolog\Logger;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model;

/**
 * @property DbService $dbService
 * @property Logger $logger
 */
class DataForm extends WebForm
{
    /** @var string */
    protected $table;

    /** @var string */
    protected $tableKey = 'id';

    /** @var string */
    protected $formTemplate = 'dataForm';

    /**
     * @param string $table
     */
    public function __construct(string $table)
    {
        parent::__construct();

        $this->table = $table;
    }

    /**
     * @inheritdoc
     * @return StorableField
     */
    public function addMultiCheckboxField(string $key, string $label, array $options): Field
    {
        return parent::addMultiCheckboxField($key, $label, $options);
    }

    /**
     * @return StorableField[]
     */
    public function getFields(): array
    {
        return parent::getFields();
    }

    /**
     * @param array $input
     * @return ErrorContainer
     */
    public function validate(array $input): ErrorContainer
    {
        return new ErrorContainer();
    }

    /**
     * @param array $input
     * @return void
     */
    public function successAction(array $input)
    {
        $editId = $this->saveData($input);

        if($editId && !array_key_exists(DataTable::EDIT_ID, $input)){
            $this->addHiddenField(DataTable::EDIT_ID, $editId);
        }

        if ($editId) {
            $this->flash->success($this->translator->tl('dataForm.saveSuccess'));
        } else {
            $this->response->setStatusCode(StatusCodes::FORM_INVALID, StatusCodes::FORM_INVALID_MESSAGE);
            $this->flash->error($this->translator->tl('dataForm.saveFailure'));
        }
    }

    /**
     * @param array $editData
     * @return Response|string
     */
    public function renderWithData(array $editData)
    {
        /** @var StorableField $field */
        foreach ($this->fields as $key => &$field) {
            if (array_key_exists($key, $editData)) {
                $field->getElement()->setDefault($editData[$key]);
            }
        }

        return $this->render();
    }

    /**
     * @return Field|StorableField
     */
    protected function createNewField(): Field
    {
        return new StorableField();
    }

    /**
     * @param array $input
     * @return mixed
     */
    private function saveData(array $input)
    {
        $insertUpdateData = $this->getInsertUpdateData($input);

        /** @var Model $model */
        $model = new $this->table();
        $table = $model->getSource();

        $this->db->begin();

        try {
            if (isset($input[DataTable::EDIT_ID])) {
                $editId = $input[DataTable::EDIT_ID];
                $this->dbService->update($table, $insertUpdateData, [$this->tableKey => $editId]);
            } else {
                $editId = $this->dbService->insert($table, $insertUpdateData);
            }

            $this->storeFields($input, $editId);
        } catch (Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception);
            $this->db->rollback();

            return false;
        }

        $this->db->commit();

        return $editId;
    }

    /**
     * Get an array of formFields that are used by the system and don't contain user input
     *
     * @return array
     */
    private function getSystemFields()
    {
        return [WebForm::WEB_FORM_ID, DataTable::EDIT_ID, DataTable::INSTANCE, DataTable::PAGE];
    }

    /**
     * Create an array with data from the form that can be inserted or updated directly
     *
     * @param array $input
     * @return array
     */
    private function getInsertUpdateData(array $input): array
    {
        $insertUpdateData = [];

        /** @var StorableField $field */
        foreach ($this->fields as $key => $field) {
            if (in_array($key, $this->getSystemFields())) {
                continue;
            }

            // will be saved in another table, so skip here
            if ($field->isStoredElsewhere()) {
                continue;
            }

            // in case of a checkbox, set the value by its existence
            if ($field->getType() == Field::TYPE_CHECKBOX) {
                $input[$key] = isset($input[$key]) ? 1 : 0;
            }

            $insertUpdateData[$key] = $this->formatInputValue($input[$key]);
        }

        return $insertUpdateData;
    }

    /**
     * Format the forms' input for database insertion
     *
     * @param mixed $value
     * @return mixed|null
     */
    private function formatInputValue($value)
    {
        // convert empty string to null
        if ($value === '') {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return $value;
    }

    /**
     * @param array $input
     * @param $editId
     */
    private function storeFields(array $input, $editId)
    {
        /** @var StorableField $field */
        foreach ($this->fields as $key => $field) {
            if ( ! $field->isStoredElsewhere()) {
                continue;
            }

            if ($field->getType() == Field::TYPE_MULTI_CHECKBOX) {
                /** @var MultiCheckbox $element */
                $element     = $field->getElement();
                $table       = $field->getTable();
                $relationKey = $field->getFieldStorage()->getRelationKey();

                $ids            = array_keys($element->getOptions());
                $whereCondition = $relationKey . ' = ' . $editId . ' AND ' . $key . ' IN (' . implode(',', $ids) . ')';

                $this->db->delete($table, $whereCondition);

                if ( ! isset($input[$key])) {
                    continue;
                }

                foreach ($input[$key] as $id) {
                    $this->db->insert($table, [$editId, $id], [$relationKey, $key]);
                }
            }
        }
    }
}