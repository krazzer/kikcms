<?php declare(strict_types=1);

namespace Website\Models;

use KikCmsCore\Classes\Model;

class Person extends Model
{
    const TABLE = 'test_person';
    const ALIAS = 'p';

    const FIELD_ID            = 'id';
    const FIELD_NAME          = 'name';
    const FIELD_COMPANY_ID    = 'company_id';
    const FIELD_IMAGE_ID      = 'image_id';
    const FIELD_DISPLAY_ORDER = 'display_order';
    const FIELD_CREATED       = 'created';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->belongsTo(self::FIELD_COMPANY_ID, Company::class, Company::FIELD_ID, ['alias' => 'company']);
        $this->hasMany(self::FIELD_ID, PersonInterest::class, PersonInterest::FIELD_PERSON_ID, ['alias' => 'personInterests']);
        $this->hasMany(self::FIELD_ID, PersonImage::class, PersonImage::FIELD_PERSON_ID, ['alias' => 'personImages']);
    }
}
