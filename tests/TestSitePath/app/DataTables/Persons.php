<?php declare(strict_types=1);

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\PersonForm;
use Website\Models\Person;

class Persons extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return PersonForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['person', 'persons'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Person::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            Person::FIELD_ID => 'Id',
            Person::FIELD_NAME => 'Name',
            Person::FIELD_COMPANY_ID => 'Company_id',
            Person::FIELD_IMAGE_ID => 'Image_id',
            Person::FIELD_DISPLAY_ORDER => 'Display_order',
            Person::FIELD_CREATED => 'Created',
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
