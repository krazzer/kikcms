<?php declare(strict_types=1);

namespace KikCMS\Models;


use KikCmsCore\Classes\Model;

class Folder extends File
{
    /** @var int */
    public $is_folder = 1;

    /** @var int */
    public $size = 0;

    /**
     * @inheritdoc
     *
     * @return Folder|Model
     */
    public static function getById($id): ?File
    {
        return parent::getById($id);
    }
}