<?php declare(strict_types=1);

namespace Website\Forms;

use KikCMS\Classes\WebForm\DataForm\DataForm;
use Website\Models\Interest;

class InterestForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Interest::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // add form code...
    }
}
