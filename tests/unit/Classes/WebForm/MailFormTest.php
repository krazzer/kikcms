<?php

namespace KikCMS\Classes\WebForm;

use Codeception\Test\Unit;
use Helpers\Forms\TestMailForm;
use Helpers\TestHelper;
use Phalcon\Di;
use Phalcon\Validation;

class MailFormTest extends Unit
{
    public function testToMailOutput()
    {
        $di = new Di();
        $di->set('validation', new Validation);
        $di->set('translator', (new TestHelper)->getTranslator());

        Di::setDefault($di);

        $mailForm = new TestMailForm();

        $mailForm->initializeForm();

        $input = [
            'test' => 'test',
            $mailForm->getFormId() => 'formId',
            'select' => 'key1',
            'text' => 'TextValue',
            'text2' => [1,2,3],
            'text3' => '',
        ];

        $expected = '<b>Select:</b><br>value1<br><br>';
        $expected .= '<b>Text:</b><br>TextValue<br><br>';
        $expected .= '<b>Text2:</b><br>1<br />2<br />3<br><br>';
        $expected .= '<b>Text3:</b><br>-<br><br>';
        $expected .= '<b>Check:</b><br>-<br><br>';

        $this->assertEquals($expected, $mailForm->toMailOutput($input));

        // test with checked checkbox
        $input = [
            'test' => 'test',
            $mailForm->getFormId() => 'formId',
            'select' => 'key1',
            'text' => 'TextValue',
            'text2' => [1,2,3],
            'text3' => '',
            'check' => 'on',
        ];

        $expected = '<b>Select:</b><br>value1<br><br>';
        $expected .= '<b>Text:</b><br>TextValue<br><br>';
        $expected .= '<b>Text2:</b><br>1<br />2<br />3<br><br>';
        $expected .= '<b>Text3:</b><br>-<br><br>';
        $expected .= '<b>Check:</b><br>✔︎<br><br>';

        $this->assertEquals($expected, $mailForm->toMailOutput($input));
    }
}