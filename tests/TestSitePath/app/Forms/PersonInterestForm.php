<?php declare(strict_types=1);

namespace Website\Forms;

use KikCMS\Classes\WebForm\DataForm\DataForm;
use Website\Models\PersonInterest;

class PersonInterestForm extends DataForm
{
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
    protected function initialize()
    {
        // add form code...
    }
}
