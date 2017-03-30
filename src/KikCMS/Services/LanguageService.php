<?php

namespace KikCMS\Services;

/**
 * Service for managing different languages for the website, and also for configuring these in the CMS
 */
class LanguageService
{
    /**
     * @return string
     */
    public function getDefaultLanguageCode(): string
    {
        //todo: actually get this form the db
        return 'nl';
    }
}