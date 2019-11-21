<?php declare(strict_types=1);

namespace Website\Forms;

use KikCMS\Classes\WebForm\DataForm\DataForm;
use Website\Models\PersonInterestNoId;

class PersonInterestNoIdForm extends DataForm
{
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
    protected function initialize()
    {
        // add form code...
    }
}
