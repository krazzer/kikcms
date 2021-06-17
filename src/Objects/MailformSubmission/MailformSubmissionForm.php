<?php declare(strict_types=1);

namespace KikCMS\Objects\MailformSubmission;

use KikCMS\Classes\WebForm\DataForm\DataForm;

class MailformSubmissionForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return MailformSubmission::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        // add form code...
    }
}
