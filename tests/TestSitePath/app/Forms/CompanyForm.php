<?php declare(strict_types=1);

namespace Website\Forms;

use KikCMS\Classes\WebForm\DataForm\DataForm;
use Website\Models\Company;

class CompanyForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Company::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // add form code...
    }
}
