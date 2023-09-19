<?php declare(strict_types=1);

namespace KikCMS\Objects\MailformSubmission;

use KikCMS\Classes\DataTable\DataTable;
use Phalcon\Mvc\Model\Query\BuilderInterface;

class MailformSubmissions extends DataTable
{
    /**
     * @inheritDoc
     */
    public function getDefaultQuery(): BuilderInterface
    {
        return parent::getDefaultQuery()->orderBy(MailformSubmission::FIELD_CREATED . ' DESC');
    }

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
        return [
            $this->translator->tl('dataTables.mailFormSubmissions.singular'),
            $this->translator->tl('dataTables.mailFormSubmissions.plural'),
        ];
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
            MailformSubmission::FIELD_ID      => 'Id',
            MailformSubmission::FIELD_CREATED => $this->translator->tl('contentTypes.date'),
            MailformSubmission::FIELD_SUBJECT => $this->translator->tl('global.subject'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $this->setFieldFormatting(MailformSubmission::FIELD_CREATED, [$this->dateTimeService, 'stringToDateTimeFormat']);
    }
}
