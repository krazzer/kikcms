<?php declare(strict_types=1);

namespace KikCMS\Objects\Redirect;

use KikCMS\Classes\DataTable\DataTable;

class Redirects extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return RedirectForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return ['redirect', 'redirects'];
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Redirect::class;
    }

    /**
     * @inheritdoc
     */
    public function getTableFieldMap(): array
    {
        return [
            Redirect::FIELD_ID               => 'Id',
            Redirect::FIELD_PATH_FROM        => 'Path_from',
            Redirect::FIELD_PATH_TO          => 'Path_to',
            Redirect::FIELD_PAGE_LANGUAGE_ID => 'Page_language_id',
        ];
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        // nothing here...
    }
}
