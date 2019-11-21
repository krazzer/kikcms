<?php declare(strict_types=1);

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use Website\Forms\WorkDisciplineForm;
use Website\Models\WorkDiscipline;

class WorkDisciplines extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return WorkDisciplineForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['workdiscipline', 'workdisciplines'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return WorkDiscipline::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            WorkDiscipline::FIELD_WORK_ID => 'Work_id',
            WorkDiscipline::FIELD_DISCIPLINE_ID => 'Discipline_id',
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
