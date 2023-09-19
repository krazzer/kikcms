<?php declare(strict_types=1);

namespace KikCMS\Objects\MailformSubmission;

use KikCmsCore\Classes\Model;

class MailformSubmission extends Model
{
    const TABLE = 'cms_mailform_submission';
    const ALIAS = 'ms';

    const FIELD_ID       = 'id';
    const FIELD_CREATED  = 'created';
    const FIELD_SUBJECT  = 'subject';
    const FIELD_CONTENTS = 'contents';
}
