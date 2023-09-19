<?php declare(strict_types=1);

namespace KikCMS\Models;


class Folder extends File
{
    /** @var int */
    public $is_folder = 1;

    /** @var int */
    public $size = 0;

    /**
     * @inheritdoc
     *
     * @return File|null
     */
    public static function getById($id): ?File
    {
        return parent::getById($id);
    }
}