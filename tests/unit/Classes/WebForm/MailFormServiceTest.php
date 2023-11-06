<?php


namespace unit\Classes\WebForm;


use Helpers\Forms\TestMailForm;
use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Classes\Phalcon\Validation;
use KikCMS\Services\Website\MailFormService;
use Phalcon\Assets\Manager;
use Phalcon\Config\Config;
use Phalcon\Di\Di;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Mvc\View;

class MailFormServiceTest extends Unit
{
    public function testToMailOutput()
    {
        $di = new Di();
        $di->set('validation', new Validation);
        $di->set('translator', (new TestHelper)->getTranslator());

        Di::setDefault($di);

        $mailForm = new TestMailForm();
        $mailForm->view = new View;
        $mailForm->view->assets = new Manager(new TagFactory(new Escaper()));

        $mailForm->config = new Config();
        $mailForm->config->recaptcha = new Config();
        $mailForm->config->recaptcha->siteKey = 'key';

        $mailForm->initializeForm();

        $input = [
            'test' => 'test',
            $mailForm->getFormId() => 'formId',
            'select' => 'key1',
            'text' => 'TextValue',
            'text2' => [1,2,3],
            'text3' => '',
            'check' => false,
            'html' => 'x',
        ];

        $expected = '<b>Select:</b><br>value1<br><br>';
        $expected .= '<b>Text:</b><br>TextValue<br><br>';
        $expected .= '<b>Text2:</b><br>1<br />2<br />3<br><br>';
        $expected .= '<b>Text3:</b><br>-<br><br>';
        $expected .= '<b>Check:</b><br>-<br><br>';

        $mailFormService = new MailFormService;

        $this->assertEquals($expected, $mailFormService->getHtml($mailForm->getReadableInput($input)));

        // test with checked checkbox
        $input = [
            'test' => 'test',
            $mailForm->getFormId() => 'formId',
            'select' => 'key1',
            'text' => 'TextValue',
            'text2' => [1,2,3],
            'text3' => '',
            'check' => true,
            'hibben' => 'soundsystem',
        ];

        $expected = '<b>Select:</b><br>value1<br><br>';
        $expected .= '<b>Text:</b><br>TextValue<br><br>';
        $expected .= '<b>Text2:</b><br>1<br />2<br />3<br><br>';
        $expected .= '<b>Text3:</b><br>-<br><br>';
        $expected .= '<b>Check:</b><br>✔︎<br><br>';
        $expected .= '<b>Hibben:</b><br>soundsystem<br><br>';

        $this->assertEquals($expected, $mailFormService->getHtml($mailForm->getReadableInput($input)));
    }
}