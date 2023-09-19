<?php


namespace Helpers;


class EditableDataTable extends TestableDataTable
{
    public function canEdit(int $id = null): bool
    {
        return true;
    }
}