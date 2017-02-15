<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use KikCMS\Models\FinderFile;

class FileField extends Field
{
    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_FILE;
    }

    /**
     * @param int $fileId
     *
     * @return FinderFile
     */
    public function getFinderFileById(int $fileId): FinderFile
    {
        return FinderFile::getById($fileId);
    }
}