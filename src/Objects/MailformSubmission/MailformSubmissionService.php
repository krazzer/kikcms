<?php declare(strict_types=1);

namespace KikCMS\Objects\MailformSubmission;

use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\MailFormConfig;

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

    /**
     * @return int
     */
    public function getUploadsFolderId(): int
    {
        if($folder = $this->fileService->getByKey(MailFormConfig::UPLOADS_FOLDER_KEY)){
            return $folder->getId();
        }

        return $this->fileService->createFolder('Mailform uploads', null, MailFormConfig::UPLOADS_FOLDER_KEY);
    }
}
