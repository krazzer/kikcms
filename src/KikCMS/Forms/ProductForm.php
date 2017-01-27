<?php

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\DataTables\SubProducts;
use KikCMS\Models\DummyProducts;
use KikCMS\Models\ProductType;
use KikCMS\Models\Type;
use Phalcon\Validation\Validator\PresenceOf;

class ProductForm extends DataForm
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        $typeNameMap = Type::findAssoc();

        $this->addTextField('title', 'Naam', [new PresenceOf()]);

        $this->addDataTableField(new SubProducts(), "Sub producten");

        $this->addTextField('price', 'Prijs');
        $this->addTextField('stock', 'Voorraad');
        $this->addAutoCompleteField('category_id', 'Categorie')->setSourceModel(Type::class);
        $this->addCheckboxField('sale', 'Sale');
        $this->addWysiwygField('description', 'Omschrijving')->getElement()->setAttribute('style', 'height:350px;');

        $this->addMultiCheckboxField(ProductType::FIELD_TYPE_ID, 'Typen', $typeNameMap)
            ->table(ProductType::class, ProductType::FIELD_PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return DummyProducts::class;
    }
}