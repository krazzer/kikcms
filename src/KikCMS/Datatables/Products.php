<?php

namespace KikCMS\Datatables;


use KikCMS\Classes\Datatable\DataTable;
use Phalcon\Validation\Validator\PresenceOf;

class Products extends DataTable
{
    protected function getTable(): string
    {
        return 'products_dummy';
    }

    protected function initialize()
    {
        $this->form->addTextField('title', 'Naam', [new PresenceOf()]);
        $this->form->addTextField('price', 'Prijs');
        $this->form->addTextField('stock', 'Voorraad');
        $this->form->addTextField('sale', 'Sale');
    }
}