<?php declare(strict_types=1);

namespace KikCMS\Objects\MailformSubmission;

use KikCmsCore\Classes\ObjectList;

class MailformSubmissionList extends ObjectList
{
    /**
     * @inheritdoc
     * @return MailformSubmission|false
     */
    public function current(): MailformSubmission|false
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return MailformSubmission|false
     */
    public function get($key): MailformSubmission|false
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return MailformSubmission|false
     */
    public function getFirst(): MailformSubmission|false
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return MailformSubmission|false
     */
    public function getLast(): MailformSubmission|false
    {
        return parent::getLast();
    }
}
