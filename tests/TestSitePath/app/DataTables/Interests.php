<?php declare(strict_types=1);

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\InterestForm;
use Website\Models\Interest;

class Interests extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return InterestForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['interest', 'interests'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Interest::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            Interest::FIELD_ID => 'Id',
            Interest::FIELD_NAME => 'Name',
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
