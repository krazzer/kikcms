<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\DataTables\SubProducts;
use KikCMS\Models\DummyProducts;
use Phalcon\Validation\Validator\PresenceOf;

class ProductSubForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        $this->addTextField('title', 'Naam', [new PresenceOf()]);
        $this->addTextField('price', 'Prijs');
        $this->addTextField('stock', 'Voorraad');
        $this->addCheckboxField('sale', 'Sale');
        $this->addFileField('image_id', 'Afbeelding');

        $this->addDataTableField(new SubProducts(), "Sub producten");
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return DummyProducts::class;
    }
}