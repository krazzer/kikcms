<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\DataTables\SubProducts;
use KikCMS\Models\DummyProducts;
use KikCMS\Models\Type;
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
        $this->addAutoCompleteField('category_id', 'Categorie')->setSourceModel(Type::class);

        $this->addDataTableField(new SubProducts(), "Sub producten");

        $this->addWysiwygField('description', 'Omschrijving');
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return DummyProducts::class;
    }
}