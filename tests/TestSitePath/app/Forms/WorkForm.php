<?php declare(strict_types=1);

namespace Website\Forms;

use KikCMS\Classes\WebForm\DataForm\DataForm;
use Website\Models\Work;

class WorkForm extends DataForm
{
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
    protected function initialize()
    {
        // add form code...
    }
}
