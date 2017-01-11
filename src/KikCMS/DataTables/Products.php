<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Models\DummyProducts;
use KikCMS\Models\ProductType;
use KikCMS\Models\Type;
use Phalcon\Mvc\Model\Resultset;
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
        /** @var Resultset $typesResult */
        $typesResult = Type::find();
        $types = $typesResult->toArray();
        $typeAssoc = [];

        foreach($types as $type){
            $typeAssoc[$type['id']] = $type['name'];
        }

        $this->form->addTextField('title', 'Naam', [new PresenceOf()]);
        $this->form->addTextField('price', 'Prijs');
        $this->form->addTextField('stock', 'Voorraad');
        $this->form->addCheckboxField('sale', 'Sale');

        $this->form->addMultiCheckboxField(ProductType::FIELD_TYPE_ID, 'Typen', $typeAssoc)
            ->table(ProductType::class, ProductType::FIELD_PRODUCT_ID);
    }
}