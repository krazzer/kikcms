<?php declare(strict_types=1);

namespace Website\Models;

use KikCmsCore\Classes\Model;

class WorkDiscipline extends Model
{
    const TABLE = 'test_work_discipline';
    const ALIAS = 'wd';
    const FIELD_WORK_ID = 'work_id';
    const FIELD_DISCIPLINE_ID = 'discipline_id';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();
    }
}
