<?php declare(strict_types=1);


namespace KikCMS\Services;


use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Translator;
use KikCMS\Config\CacheConfig;
use KikCMS\Config\PlaceholderConfig;
use KikCMS\Services\Pages\UrlService;
use Phalcon\Config;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Tag;

/**
 * @property AccessControl $acl
 * @property AssetService $assetService
 * @property Translator $translator
 * @property PlaceholderService $placeholderService
 * @property UrlService $urlService
 */
class TwigService extends Injectable
{
    /** @var string */
    private string $mediaStoragePath;

    /** @var string */
    private string $iconStoragePath;

    /**
     * @param string $mediaStoragePath
     * @param string $iconStoragePath
     */
    public function __construct(string $mediaStoragePath, string $iconStoragePath)
    {
        $this->mediaStoragePath = $mediaStoragePath;
        $this->iconStoragePath  = $iconStoragePath;
    }

    /**
     * @param string $resourceName
     * @param string $access
     * @param array|null $parameters
     * @return bool
     */
    public function allowed(string $resourceName, $access = '*', array $parameters = null): bool
    {
        return $this->acl->allowed($resourceName, $access, $parameters);
    }

    /**
     * @param string $string
     * @return string
     */
    public function config(string $string): string
    {
        $string = explode('.', $string);

        /** @var Config $configGroup */
        $configGroup = $this->config->get($string[0]);

        if ( ! $configGroup) {
            return '';
        }

        return (string) $configGroup->get($string[1]);
    }

    /**
     * @param string $url
     * @return string
     */
    public function css(string $url): string
    {
        $url = $this->assetService->addVersion($url);

        $parameters = [$url, true];

        return Tag::stylesheetLink($parameters);
    }

    /**
     * @return string
     */
    public function endForm(): string
    {
        return Tag::endForm();
    }

    /**
     * @param mixed $fileId
     * @param string|null $thumb
     * @param bool $private
     * @return string
     */
    public function mediaFile($fileId, string $thumb = null, $private = false): string
    {
        if ( ! $fileId) {
            return '';
        }

        $private = $private ? 'private' : 'public';

        if ( ! $thumb) {
            return $this->placeholderService->getValue(PlaceholderConfig::FILE_URL, $fileId, $private);
        }

        return $this->placeholderService->getValue(PlaceholderConfig::FILE_THUMB_URL, $fileId, $thumb, $private);
    }

    /**
     * @param int|null $fileId
     * @param string|null $thumb
     * @param bool $private
     * @return string
     */
    public function mediaFileBg(?int $fileId, string $thumb = null, bool $private = false): string
    {
        return "background-image: url('" . $this->mediaFile($fileId, $thumb, $private) . "');";
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function form(array $parameters = []): string
    {
        return Tag::form($parameters);
    }

    /**
     * @param int|mixed|string $pageId
     * @return string
     */
    public function pageUrl($pageId): string
    {
        $langCode = $this->translator->getLanguageCode();

        if (is_string($pageId) && strstr($pageId, '/')) {
            return $pageId;
        }

        $cacheKey = CacheConfig::getUrlKey($pageId, $langCode);

        return $this->cacheService->cache($cacheKey, function () use ($pageId, $langCode){
            if (is_numeric($pageId)) {
                return $this->urlService->getUrlByPageId((int) $pageId, $langCode);
            }

            return $this->urlService->getUrlByPageKey($pageId, $langCode);
        });
    }

    /**
     * @param string $url
     * @return string
     */
    public function js(string $url): string
    {
        $url = $this->assetService->addVersion($url);

        $parameters = [$url, true];

        return Tag::javascriptInclude($parameters);
    }

    /**
     * @param string $value
     * @param array $parameters
     * @return string
     */
    public function submitButton(string $value, array $parameters = []): string
    {
        return Tag::submitButton(['value' => $value] + $parameters);
    }

    /**
     * @param string|int $value
     * @return string
     */
    public function svg($value): string
    {
        if (is_numeric($value)) {
            $filePath = $this->mediaStoragePath . $value . '.svg';
        } else {
            $filePath = $this->iconStoragePath . $value . '.svg';
        }

        if ( ! file_exists($filePath)) {
            return '?';
        }

        return file_get_contents($filePath);
    }

    /**
     * @param string|null $string
     * @param array $parameters
     * @return string
     */
    public function tl(?string $string, array $parameters = []): string
    {
        return $this->translator->tl($string, $parameters);
    }

    /**
     * @param string $string
     * @return string
     */
    public function ucfirst(string $string): string
    {
        return ucfirst($string);
    }

    /**
     * @param string $route
     * @param null $args
     * @return string
     */
    public function url(string $route, $args = null): string
    {
        return $this->url->get($route, $args);
    }
}