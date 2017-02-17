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

        $productTypeField = $this->addMultiCheckboxField(ProductType::FIELD_TYPE_ID, 'Typen', $typeNameMap);
        $productTypeField->table(ProductType::class, ProductType::FIELD_PRODUCT_ID);

        $this->addTab('Algemeen', [
            $this->addTextField('title', 'Naam', [new PresenceOf()]),
            $this->addFileField('image_id', 'Afbeelding', [new PresenceOf()]),
            $this->addTextField('price', 'Prijs'),
            $this->addTextField('stock', 'Voorraad'),
            $this->addCheckboxField('sale', 'Sale'),
        ]);

        $this->addTab('Sub producten', [
            $subProductsField = $this->addDataTableField(new SubProducts(), "Sub producten")
        ]);

        $categories = Type::findAssoc();
        $categories = ['' => ''] + $categories;

        $this->addTab('Omschrijving', [
            $this->addWysiwygField('description', 'Omschrijving'),
            //$this->addAutoCompleteField('category_id', 'Categorie')->setSourceModel(Type::class),
            $this->addSelectField('category_id', 'Categorie', $categories, [new PresenceOf()]),
            $this->addDateField('available', 'Beschikbaar vanaf'),
            $productTypeField
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return DummyProducts::class;
    }
}