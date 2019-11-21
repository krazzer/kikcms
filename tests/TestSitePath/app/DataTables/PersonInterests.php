<?php declare(strict_types=1);

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\PersonInterestForm;
use Website\Models\PersonInterest;

class PersonInterests extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return PersonInterestForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['personinterest', 'personinterests'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return PersonInterest::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            PersonInterest::FIELD_ID => 'Id',
            PersonInterest::FIELD_PERSON_ID => 'Person_id',
            PersonInterest::FIELD_INTEREST_ID => 'Interest_id',
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
