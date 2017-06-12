<?php

namespace KikCMS\Classes\Exceptions;


class NotFoundException extends \Exception
{
    /** @var string */
    private $languageCode;

    /**
     * @param string|null $languageCode
     */
    public function __construct(string $languageCode = null)
    {
        $this->languageCode = $languageCode;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * @param string $languageCode
     * @return NotFoundException
     */
    public function setLanguageCode(string $languageCode): NotFoundException
    {
        $this->languageCode = $languageCode;
        return $this;
    }
}