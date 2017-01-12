<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Models\DummyProducts;
use KikCMS\Models\ProductType;
use KikCMS\Models\Type;
use Phalcon\Validation\Validator\PresenceOf;

class Products extends DataTable
{
    /** @inheritdoc */
    protected $searchableFields = ['title', 'description'];

    /**
     * @inheritdoc
     */
    protected function getTable(): string
    {
        return DummyProducts::class;
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $typeNameMap = Type::findAssoc();

        $this->form->addTextField('title', 'Naam', [new PresenceOf()]);
        $this->form->addTextField('price', 'Prijs');
        $this->form->addTextField('stock', 'Voorraad');
        $this->form->addCheckboxField('sale', 'Sale');
        $this->form->addWysiwygField('description', 'Omschrijving')->getElement()->setAttribute('style', 'height:350px;');

        $this->form->addMultiCheckboxField(ProductType::FIELD_TYPE_ID, 'Typen', $typeNameMap)
            ->table(ProductType::class, ProductType::FIELD_PRODUCT_ID);

        $this->setFieldFormatting('price', function($value){
            return '&euro; ' . number_format($value, 2, ',', '.');
        });

        $this->setFieldFormatting('sale', function($value){
            return $value == 1 ? '<span style="color:green;" class="glyphicon glyphicon-ok"></span>' : '';
        });

        $this->setFieldFormatting('description', function($value){
            $value = html_entity_decode(strip_tags($value));

            if(mb_strlen($value) > 50){
                return mb_substr($value, 0, 50) . '...';
            }

            return $value;
        });
    }
}