<?php

namespace Website\Forms;

use KikCMS\Classes\WebForm\DataForm\DataForm;
use Website\Models\SimpleObject;

class SimpleObjectForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return SimpleObject::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // add form code...
    }
}
