<?php

namespace Website\Forms;

use KikCMS\Classes\WebForm\DataForm\DataForm;
use Website\Models\DataTableTestChild;

class DatatableTestChildForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return DataTableTestChild::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // add form code...
    }
}
