<?php


namespace Helpers;


class EditableDataTable extends TestableDataTable
{
    public function canEdit($id = null): bool
    {
        return true;
    }
}