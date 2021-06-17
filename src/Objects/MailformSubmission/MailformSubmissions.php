<?php declare(strict_types=1);

namespace KikCMS\Objects\MailformSubmission;

use KikCMS\Classes\DataTable\DataTable;

class MailformSubmissions extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return MailformSubmissionForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['mailformsubmission', 'mailformsubmissions'];
    }

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
    public function getTableFieldMap(): array
    {
        return [
            MailformSubmission::FIELD_ID       => 'Id',
            MailformSubmission::FIELD_CREATED  => 'Created',
            MailformSubmission::FIELD_SUBJECT  => 'Subject',
            MailformSubmission::FIELD_CONTENTS => 'Contents',
        ];
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        // nothing here...
    }
}
