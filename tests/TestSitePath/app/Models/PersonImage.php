<?php declare(strict_types=1);

namespace Website\Models;

use KikCMS\Models\File;
use KikCmsCore\Classes\Model;

class PersonImage extends Model
{
    const TABLE = 'test_person_image';
    const ALIAS = 'pi';

    const FIELD_ID        = 'id';
    const FIELD_IMAGE_ID  = 'image_id';
    const FIELD_PERSON_ID = 'person_id';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->belongsTo(self::FIELD_PERSON_ID, Person::class, Person::FIELD_ID, ['alias' => 'person']);
        $this->hasOne(self::FIELD_IMAGE_ID, File::class, File::FIELD_ID, ['alias' => 'file']);
    }
}
