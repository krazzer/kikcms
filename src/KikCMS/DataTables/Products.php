<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Models\DummyProducts;
use KikCMS\Models\ProductType;
use KikCMS\Models\Type;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Validation\Validator\PresenceOf;

class Products extends DataTable
{
    /** @inheritdoc */
    protected $searchableFields = ['title', 'description', 'name'];

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
    protected function getDefaultQuery()
    {
        $defaultQuery = new Builder();
        $defaultQuery->addFrom($this->getTable(), 'p');
        $defaultQuery->leftJoin(Type::class, 't.id = p.category_id', 't');
        $defaultQuery->columns(['p.id', 'p.title', 'p.price', 'p.stock', 't.name as category', 'p.description']);
        $defaultQuery->orderBy('title ASC');

        return $defaultQuery;
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
        $this->form->addAutoCompleteField('category_id', 'Categorie')->setSourceTableModel(Type::class);

        $this->form->addCheckboxField('sale', 'Sale');
        $this->form->addWysiwygField('description', 'Omschrijving')->getElement()->setAttribute('style', 'height:350px;');

        $this->form->addMultiCheckboxField(ProductType::FIELD_TYPE_ID, 'Typen', $typeNameMap)
            ->table(ProductType::class, ProductType::FIELD_PRODUCT_ID);

        $this->setFieldFormatting('price', function($value){
            return '&euro;&nbsp;' . number_format($value, 2, ',', '.');
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