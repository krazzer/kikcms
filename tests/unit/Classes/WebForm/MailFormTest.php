<?php

namespace unit\Classes\WebForm;

use Helpers\Forms\TestMailForm;
use Helpers\TestHelper;
use Helpers\Unit;
use KikCMS\Classes\Phalcon\View;
use KikCMS\Classes\Translator;
use KikCMS\Services\MailService;
use Phalcon\Assets\Manager;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Flash\Direct;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Router;
use Phalcon\Session\AdapterInterface;
use Phalcon\Validation;
use PHPUnit\Framework\MockObject\MockObject;
use ReCaptcha\Response as ReCaptchaResponse;

class MailFormTest extends Unit
{
    public function testSuccessAction()
    {
        $view = $this->createMock(View::class);
        $view->method('getPartial')->willReturn('');
        $view->method('__get')->willReturn(new Manager);

        $translator = $this->createMock(Translator::class);
        $translator->method('tl')->willReturn('');

        $mailForm = new TestMailForm();
        $mailForm->view = $view;
        $mailForm->translator = $translator;
        $mailForm->session = $this->createMock(AdapterInterface::class);
        $mailForm->flash = $this->createMock(Direct::class);
        $mailForm->request = $this->createMock(Request::class);

        $mailForm->config = new Config();
        $mailForm->config->recaptcha = new Config();
        $mailForm->config->recaptcha->siteKey = 'key';

        $mailForm->config->application = new Config();
        $mailForm->config->application->adminEmail = 'test@test.nl';

        $response = $this->createMock(Response::class);
        $response->method('redirect')->willReturn('redirect');

        $router = $this->createMock(Router::class);
        $router->method('getRewriteUri')->willReturn('');

        $mailForm->mailService = $this->getMailService(1);
        $mailForm->response = $response;
        $mailForm->router = $router;

        // all ok, will redirect
        $mailForm->validation = $this->getValidation(0.8);
        $mailForm->initializeForm();
        $this->assertEquals('redirect', $this->invokeMethod($mailForm, 'successAction', [['check' => true]]));

        // spamscore too low, won't send
        $mailForm->validation = $this->getValidation(0.2);
        $this->assertFalse($this->invokeMethod($mailForm, 'successAction', [['check' => true]]));

        // mail could not be send
        $mailForm->validation = $this->getValidation(1);
        $mailForm->mailService = $this->getMailService(0);
        $this->assertFalse($this->invokeMethod($mailForm, 'successAction', [['check' => true]]));

        // no validators
        $validation = $this->createMock(Validation::class);
        $validation->method('getValidators')->willReturn([]);

        $mailForm->validation = $validation;

        $this->assertFalse($this->invokeMethod($mailForm, 'successAction', [['check' => true]]));

        // no captcha field
        $mailForm->getFieldMap()->remove('captcha');
        $mailForm->validation = $this->getValidation(1);
        $this->assertFalse($this->invokeMethod($mailForm, 'successAction', [['check' => true]]));
    }

    public function testToMailOutput()
    {
        $di = new Di();
        $di->set('validation', new Validation);
        $di->set('translator', (new TestHelper)->getTranslator());

        Di::setDefault($di);

        $mailForm = new TestMailForm();
        $mailForm->view = new View;
        $mailForm->view->assets = new Manager;

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

        $this->assertEquals($expected, $mailForm->toMailOutput($input));

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

        $this->assertEquals($expected, $mailForm->toMailOutput($input));
    }

    /**
     * @param float $returnScore
     * @return MockObject|Validation
     */
    private function getValidation(float $returnScore): MockObject
    {
        $reCaptchaResponse = $this->createMock(ReCaptchaResponse::class);
        $reCaptchaResponse->method('getScore')->willReturn($returnScore);

        $validator = $this->createMock(Validation\Validator::class);
        $validator->method('getOption')->willReturn($reCaptchaResponse);

        $validation = $this->createMock(Validation::class);
        $validation->method('getValidators')->willReturn([['captcha', $validator]]);

        return $validation;
    }

    /**
     * @param int $return
     * @return MockObject|MailService
     */
    private function getMailService(int $return): MockObject
    {
        $mailService = $this->createMock(MailService::class);
        $mailService->method('sendServiceMail')->willReturn($return);

        return $mailService;
    }
}