<?php


namespace Helpers\Forms;


use KikCMS\Classes\WebForm\MailForm;

class TestMailForm extends MailForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addSelectField('select', 'Select', ['key1' => 'value1']);
        $this->addTextField('text', 'Text');
        $this->addTextField('text2', 'Text2');
        $this->addTextField('text3', 'Text3');
        $this->addHtmlField('html', 'Html', 'Content');
        $this->addCheckboxField('check', 'Check');
        $this->addRecaptchaField('captcha', 3);
        $this->addHiddenField('hibben', 'soundsystem');
    }
}