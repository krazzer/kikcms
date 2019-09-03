<?php
declare(strict_types=1);

namespace Forms;

use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Translator;
use KikCMS\Forms\PageForm;
use KikCMS\Services\Pages\UrlService;

class PageFormTest extends \Codeception\Test\Unit
{
    public function testValidate()
    {
        // can't edit menu
        $pageForm = new PageForm();

        $acl = $this->createMock(AccessControl::class);
        $acl->method('allowed')->willReturn(false);

        $translator = $this->createMock(Translator::class);
        $translator->method('tl')->willReturn('xxx');

        $pageForm->acl = $acl;
        $pageForm->translator = $translator;

        $result = $pageForm->validate(['type' => 'menu', 'pageLanguage*:slug' => '']);
        $this->assertTrue($result->hasFormErrors());

        // type is not menu or page
        $pageForm = new PageForm();

        $result = $pageForm->validate(['type' => 'link', 'pageLanguage*:slug' => '']);
        $this->assertFalse($result->hasFormErrors());

        // no slug
        $pageForm = new PageForm();

        $result = $pageForm->validate(['type' => 'page', 'pageLanguage*:slug' => '']);
        $this->assertFalse($result->hasFormErrors());

        // url path exists
        $pageForm = new PageForm();

        $urlService = $this->createMock(UrlService::class);
        $urlService->method('urlPathExists')->willReturn(true);

        $translator = $this->createMock(Translator::class);
        $translator->method('tl')->willReturn('xxx');

        $pageForm->urlService = $urlService;
        $pageForm->translator = $translator;

        $result = $pageForm->validate(['type' => 'page', 'pageLanguage*:slug' => 'x']);

        $this->assertTrue($result->hasFieldErrors());
    }
}
