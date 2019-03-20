<?php

namespace KikCMS\Classes\WebForm\DataForm;


use Forms\PersonForm;
use Helpers\TestHelper;
use PHPUnit\Framework\TestCase;

class DataFormTest extends TestCase
{
    public function testRender()
    {
        $di = (new TestHelper)->getTestDi();

        $personForm = new PersonForm();

        $personForm->setDI($di);

        $response = $personForm->render();

        $this->assertContains('<div class="webForm"', $response);
    }
}
