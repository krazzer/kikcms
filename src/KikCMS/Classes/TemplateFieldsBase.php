<?php

namespace KikCMS\Classes;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Classes\WebForm\WebForm;
use Phalcon\Di\Injectable;

class TemplateFieldsBase extends Injectable
{
    /** @var DataForm */
    private $webForm;

    /**
     * @return WebForm
     */
    public function getWebForm(): WebForm
    {
        return $this->webForm;
    }

    /**
     * @param WebForm $webForm
     * @return TemplateFieldsBase
     */
    public function setWebForm(WebForm $webForm): TemplateFieldsBase
    {
        $this->webForm = $webForm;
        return $this;
    }
}