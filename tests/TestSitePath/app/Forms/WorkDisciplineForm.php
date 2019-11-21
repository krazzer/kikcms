<?php declare(strict_types=1);

namespace Website\Forms;

use KikCMS\Classes\WebForm\DataForm\DataForm;
use Website\Models\WorkDiscipline;

class WorkDisciplineForm extends DataForm
{
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
    protected function initialize()
    {
        // add form code...
    }
}
