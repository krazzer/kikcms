<?php

namespace Classes\WebForm;

use Helpers\Unit;
use KikCMS\Classes\WebForm\Fields\TextField;

class FieldTest extends Unit
{
    public function testClass()
    {
        $field = new TextField('key', 'label');

        // test add class
        $field->addClass('test');
        $this->assertEquals(['test'], $field->getClasses());

        // test add element class
        $field->addElementClass('test');
        $this->assertEquals(['class' => 'form-control test'], $field->getElement()->getAttributes());

        // test add element class
        $field->addElementClass('test');
        $this->assertEquals(['class' => 'form-control test'], $field->getElement()->getAttributes());

        // test add element class
        $field->setPlaceholder('test');
        $this->assertEquals('test', $field->getElement()->getAttribute('placeholder'));

        // test set attribute
        $field->setAttribute('data-test', 'test');
        $this->assertEquals('test', $field->getElement()->getAttribute('data-test'));

        // test set helptext
        $field->setHelpText('test');
        $this->assertEquals('test', $field->getHelpText());

        // test set helptext
        $field->setMaxLength(100);
        $this->assertEquals(100, $field->getElement()->getAttribute('maxlength'));
    }
}
