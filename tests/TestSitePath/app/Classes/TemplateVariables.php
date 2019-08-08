<?php

namespace Website\Classes;


use KikCMS\Classes\Frontend\Extendables\TemplateVariablesBase;
use KikCMS\Services\Website\FrontendHelper;

/**
 * @property FrontendHelper $frontendHelper
 */
class TemplateVariables extends TemplateVariablesBase
{
    /**
     * @inheritdoc
     */
    public function getGlobalVariables(): array
    {
        return [];
    }
}