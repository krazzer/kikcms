<?php


namespace Helpers;


class NonEditableDataTable extends TestableDataTable
{
    public function canEdit(int $id = null): bool
    {
        return false;
    }
}