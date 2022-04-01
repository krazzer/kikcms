<?php declare(strict_types=1);


namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\FieldTransformer;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\UrlService;

/**
 * @property UrlService $urlService
 * @property PageLanguageService $pageLanguageService
 */
class UrlToId extends FieldTransformer
{
    /** @var string */
    private $languageCode;

    /**
     * UrlToId constructor.
     * @param Field $field
     * @param string $languageCode
     */
    public function __construct(Field $field, string $languageCode)
    {
        parent::__construct($field);

        $this->languageCode = $languageCode;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function toStorage($value)
    {
        // it's a full URL, so just return as a whole
        if(strstr($value, '://')){
            return $value;
        }

        if ($pageLanguage = $this->urlService->getPageLanguageByUrlPath($value)) {
            return $pageLanguage->getPageId();
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function toDisplay($value)
    {
        if (is_numeric($value)) {
            return $this->urlService->getUrlByPageId((int) $value, $this->languageCode);
        }

        return $value;
    }
}