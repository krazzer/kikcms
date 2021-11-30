<?php

namespace unit\Classes\WebForm;

use Helpers\Forms\TestMailForm;
use Helpers\Unit;
use KikCMS\Classes\Phalcon\View;
use KikCMS\Classes\Translator;
use KikCMS\Objects\MailformSubmission\MailformSubmissionService;
use KikCMS\Services\MailService;
use KikCMS\Services\Website\MailFormService;
use Phalcon\Assets\Manager;
use Phalcon\Config;
use Phalcon\Flash\Direct;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Session\Adapter\AbstractAdapter;
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
        $mailForm->session = $this->createMock(AbstractAdapter::class);
        $mailForm->flash = $this->createMock(Direct::class);
        $mailForm->request = $this->createMock(Request::class);
        $mailForm->mailformSubmissionService = $this->createMock(MailformSubmissionService::class);

        $mailForm->config = new Config();
        $mailForm->config->recaptcha = new Config();
        $mailForm->config->recaptcha->siteKey = 'key';

        $mailForm->config->application = new Config();
        $mailForm->config->application->adminEmail = 'test@test.nl';

        $redirectResponseMock = $this->createMock(Response::class);

        $response = $this->createMock(Response::class);
        $response->method('redirect')->willReturn($redirectResponseMock);

        $request = $this->createMock(Request::class);
        $request->method('getServer')->willReturn('');

        $mailForm->mailService = $this->getMailService(1);
        $mailForm->mailFormService = new MailFormService;
        $mailForm->response = $response;
        $mailForm->request = $request;

        // all ok, will redirect
        $mailForm->validation = $this->getValidation(0.8);
        $mailForm->initializeForm();
        $this->assertEquals($redirectResponseMock, $this->invokeMethod($mailForm, 'successAction', [['check' => true]]));

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

    /**
     * @param float $returnScore
     * @return MockObject|Validation
     */
    private function getValidation(float $returnScore): MockObject
    {
        $reCaptchaResponse = $this->createMock(ReCaptchaResponse::class);
        $reCaptchaResponse->method('getScore')->willReturn($returnScore);

        $validator = $this->createMock(Validation\AbstractValidator::class);
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