<?php declare(strict_types=1);

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\WorkForm;
use Website\Models\Work;

class Works extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return WorkForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['work', 'works'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Work::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            Work::FIELD_ID => 'Id',
            Work::FIELD_NAME => 'Name',
        ];
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        // nothing here...
    }
}
