<?php declare(strict_types=1);

namespace KikCMS\Objects\MailformSubmission;

use KikCmsCore\Classes\ObjectList;

class MailformSubmissionList extends ObjectList
{
    /**
     * @inheritdoc
     * @return MailformSubmission|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return MailformSubmission|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return MailformSubmission|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return MailformSubmission|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
