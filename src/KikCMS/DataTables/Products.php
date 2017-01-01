<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Models\DummyProducts;
use Phalcon\Validation\Validator\PresenceOf;

class Products extends DataTable
{
    protected $searchableFields = ['title'];

    protected function getTable(): string
    {
        return DummyProducts::class;
    }

    protected function initialize()
    {
        $this->form->addTextField('title', 'Naam', [new PresenceOf()]);
        $this->form->addTextField('price', 'Prijs');
        $this->form->addTextField('stock', 'Voorraad');
        $this->form->addTextField('sale', 'Sale');
    }
}