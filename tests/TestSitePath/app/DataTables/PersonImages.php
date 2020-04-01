<?php declare(strict_types=1);

namespace Website\DataTables;

use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Phalcon\Validator\ImageFileType;
use Website\Models\PersonImage;

class PersonImages extends DataTable
{
    /** @inheritdoc */
    protected $directImageField = PersonImage::FIELD_IMAGE_ID;

    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['image', 'images'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return PersonImage::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            PersonImage::FIELD_ID       => 'Id',
            PersonImage::FIELD_IMAGE_ID => 'Image',
        ];
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        $this->directImageValidators = [new ImageFileType];
    }
}
