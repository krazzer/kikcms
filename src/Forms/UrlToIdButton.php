<?php declare(strict_types=1);


namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\Field;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\UrlService;

/**
 * @property UrlService $urlService
 * @property PageLanguageService $pageLanguageService
 */
class UrlToIdButton extends UrlToId
{
    /**
     * @inheritDoc
     */
    public function __construct(Field $field, string $languageCode)
    {
        parent::__construct($field, $languageCode);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function toStorage(mixed $value): mixed
    {
        if($url = $value['url'] ?? null){
            $value['url'] = parent::toStorage($url);
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return string|false|null
     */
    public function toDisplay(mixed $value): string|null|false
    {
        $value = json_decode($value, true);

        if($url = $value['url'] ?? null){
            $value['url'] = parent::toDisplay($url);
        }

        return $value ? json_encode($value) : null;
    }
}