<?php declare(strict_types=1);

namespace KikCMS\Objects\Redirect;

use KikCMS\Classes\WebForm\DataForm\DataForm;

class RedirectForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Redirect::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // add form code...
    }
}
