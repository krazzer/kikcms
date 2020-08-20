<?php

namespace unit\Classes\WebForm\DataForm;

use Codeception\Test\Unit;
use KikCMS\Classes\WebForm\DataForm\StorageData;
use KikCMS\Classes\WebForm\Fields\ButtonField;
use KikCMS\Classes\WebForm\Fields\HtmlField;
use KikCMS\Classes\WebForm\Fields\TextField;
use KikCMS\ObjectLists\FieldMap;

class StorageDataTest extends Unit
{
    public function testGetMainInput()
    {
        $storageData = new StorageData();

        $fieldMap = new FieldMap();
        $fieldMap->add(new TextField('name', 'Name'), 'name');
        $fieldMap->add((new TextField('dont', 'Name'))->dontStore(), 'dont');
        $fieldMap->add(new ButtonField('button', 'Button', 'Button', 'ButtonLabel', 'route'), 'button');
        $fieldMap->add(new HtmlField('html', 'Label', 'Content'), 'html');

        $storageData->setFieldMap($fieldMap);

        // test empty
        $this->assertEquals(['name' => null], $storageData->getMainInput());

        // test with input
        $storageData->setFormInput(['name' => 'test']);

        $this->assertEquals(['name' => 'test'], $storageData->getMainInput());

        // test non storable field types
        $storageData->setFormInput([
            'name'   => 'test',
            'dont'   => 'test',
            'button' => 'test',
            'html'   => 'test',
        ]);

        $this->assertEquals(['name' => 'test'], $storageData->getMainInput());

        // test additional input
        $storageData->addAdditionalInputValue('additional', 'test');

        $expected = [
            'name'       => 'test',
            'additional' => 'test',
        ];

        $this->assertEquals($expected, $storageData->getMainInput());
    }
}
