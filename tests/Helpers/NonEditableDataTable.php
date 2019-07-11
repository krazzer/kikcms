<?php


namespace Helpers;


class NonEditableDataTable extends TestableDataTable
{
    public function canEdit($id = null): bool
    {
        return false;
    }
}