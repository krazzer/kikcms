<?php declare(strict_types=1);

namespace KikCMS\Objects\MailformSubmission;

use KikCMS\Classes\Phalcon\Injectable;

class MailformSubmissionService extends Injectable
{
    /**
     * @param string $subject
     * @param array $contents
     */
    public function add(string $subject, array $contents)
    {
        $submission = new MailformSubmission;

        $submission->subject  = $subject;
        $submission->contents = json_encode($contents);

        $submission->save();
    }
}
