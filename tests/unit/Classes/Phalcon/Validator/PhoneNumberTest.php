<?php

namespace unit\Classes\Phalcon\Validator;


use KikCMS\Classes\Phalcon\Validator\PhoneNumber;
use Phalcon\Filter\Validation;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function testValidation()
    {
        $validation = (new Validation)
            ->add('number', new PhoneNumber);

        $messages = $validation->validate(['number' => 'AAA']);
        $this->assertEquals(1, $messages->count());

        $messages = $validation->validate(['number' => '+31 639750 502']);
        $this->assertEquals(0, $messages->count());

        $messages = $validation->validate(['number' => '(123) 639750-502']);
        $this->assertEquals(0, $messages->count());
    }
}
