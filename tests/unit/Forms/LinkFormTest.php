<?php
declare(strict_types=1);

namespace unit\Forms;

use Helpers\Unit;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Phalcon\Url;
use KikCMS\Classes\Translator;
use KikCMS\Forms\LinkForm;
use Phalcon\Filter\Validation;
use ReflectionMethod;

class LinkFormTest extends Unit
{
    public function testInitialize()
    {
        $linkForm = new LinkForm();

        $translator = $this->createMock(Translator::class);
        $translator->method('tl')->willReturn('x');

        $acl = $this->createMock(AccessControl::class);
        $acl->method('allowed')->willReturn(true);

        $linkForm->acl        = $acl;
        $linkForm->translator = $translator;
        $linkForm->validation = $this->createMock(Validation::class);
        $linkForm->url        = $this->createMock(Url::class);

        $linkForm->getFilters()->setLanguageCode('en');

        $method = new ReflectionMethod(LinkForm::class, 'initialize');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $method->invoke($linkForm);

        $this->assertNotEmpty($linkForm->getFieldMap());
    }
}
