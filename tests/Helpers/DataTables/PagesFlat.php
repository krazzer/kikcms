<?php
declare(strict_types=1);

namespace Helpers\DataTables;


class PagesFlat extends \KikCMS\DataTables\PagesFlat
{
    /**
     * @inheritDoc
     */
    function getTemplate(): string
    {
        return 'default';
    }

    /**
     * @inheritDoc
     */
    protected function initialize()
    {
        // nothing here...
    }
}