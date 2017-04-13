<?php

namespace KikCMS\Classes;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use Phalcon\Di\Injectable;

class TemplateFieldsBase extends Injectable
{
    /** @var DataForm */
    private $form;

    /**
     * @return DataForm
     */
    public function getForm(): DataForm
    {
        return $this->form;
    }

    /**
     * @param DataForm $form
     * @return TemplateFieldsBase
     */
    public function setForm(DataForm $form): TemplateFieldsBase
    {
        $this->form = $form;
        return $this;
    }
}