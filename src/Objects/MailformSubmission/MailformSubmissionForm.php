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
    protected function initialize(): void
    {
        $input = (array) json_decode($this->getObject()->contents);

        $contentsHtml = $this->mailFormService->getHtml($input);

        $dateLabel    = $this->translator->tl('contentTypes.date');
        $subjectLabel = $this->translator->tl('global.subject');

        $this->addHtmlField(MailformSubmission::FIELD_CREATED, $dateLabel, $this->getObject()->created);
        $this->addHtmlField(MailformSubmission::FIELD_SUBJECT, $subjectLabel, $this->getObject()->subject);
        $this->addHtmlField(MailformSubmission::FIELD_CONTENTS, null, $contentsHtml);
    }
}
