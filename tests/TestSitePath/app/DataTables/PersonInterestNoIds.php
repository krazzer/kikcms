<?php declare(strict_types=1);

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\PersonInterestNoIdForm;
use Website\Models\PersonInterestNoId;

class PersonInterestNoIds extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return PersonInterestNoIdForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['personinterestnoid', 'personinterestnoids'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return PersonInterestNoId::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            PersonInterestNoId::FIELD_PERSON_ID => 'Person_id',
            PersonInterestNoId::FIELD_INTEREST_ID => 'Interest_id',
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
