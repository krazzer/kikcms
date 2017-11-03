<?php

namespace KikCMS\Models;


class FinderFolder extends FinderFile
{
    /** @var int */
    public $is_folder = 1;

    /** @var int */
    public $size = 0;

    /**
     * @inheritdoc
     *
     * @return FinderFolder
     */
    public static function getById($id)
    {
        /** @var FinderFolder $finderFolder */
        $finderFolder = parent::getById($id);

        return $finderFolder;
    }
}