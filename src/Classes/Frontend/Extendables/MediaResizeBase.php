<?php declare(strict_types=1);

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;
use KikCMS\Classes\ObjectStorage\ThumbSettings;
use KikCMS\Services\Util\StringService;
use Phalcon\Image\Adapter\AbstractAdapter;
use Phalcon\Image\Exception;

/**
 * Contains methods to resize thumbnails in predefined formats
 *
 * @property StringService $stringService
 */
class MediaResizeBase extends WebsiteExtendable
{
    /**
     * @param string|null $type
     * @return bool
     */
    public function typeExists(string $type = null): bool
    {
        if ( ! $type) {
            return true;
        }

        return $this->typeMethodExists($type);
    }

    /**
     * @param AbstractAdapter $image
     * @param $width
     * @param $height
     * @param int|null $y
     * @param int|null $x
     * @throws Exception
     */
    public function crop(AbstractAdapter $image, $width, $height, int $x = null, int $y = null): void
    {
        $sourceWidth  = $image->getWidth();
        $sourceHeight = $image->getHeight();

        if ($sourceWidth < $width && $sourceHeight < $height) {
            $image->resize($width, $height);
        }

        $sourceAspectRatio  = $sourceWidth / $sourceHeight;
        $desiredAspectRatio = $width / $height;

        if ($sourceAspectRatio > $desiredAspectRatio) {
            $newHeight = $height;
            $newWidth  = (int) ($height * $sourceAspectRatio);
        } else {
            $newWidth  = $width;
            $newHeight = (int) ($width / ($sourceAspectRatio));
        }

        $x0 = $x === null ? (int) (($newWidth - $width) / 2) : $x;
        $y0 = $y === null ? (int) (($newHeight - $height) / 2) : $y;

        if ($newWidth != $sourceWidth || $newHeight != $sourceHeight) {
            $image->resize($newWidth, $newHeight);
        }

        $image->crop($width, $height, $x0, $y0);
    }

    /**
     * @param AbstractAdapter $image
     * @param int $width
     * @param int $height
     */
    public function resize(AbstractAdapter $image, int $width, int $height): void
    {
        if ($image->getWidth() < $width && $image->getHeight() < $height) {
            return;
        }

        $ratio = $image->getWidth() / $image->getHeight();

        if ($ratio < 1) {
            $width = (int) ($height * $ratio);
        } else {
            $height = (int) ($width / $ratio);
        }

        $image->resize($width, $height);
    }

    /**
     * @param AbstractAdapter $image
     * @param string $type
     */
    public function resizeByType(AbstractAdapter $image, string $type): void
    {
        if(method_exists($this, $this->getMethod($type))){
            $method = $this->getMethod($type);
            $this->$method($image);
            return;
        }

        list($class, $method) = $this->getPluginMethod($type);

        $class->$method($image);
    }

    /**
     * @param AbstractAdapter $image
     */
    public function resizeDefault(AbstractAdapter $image): void
    {
        $this->resize($image, 192, 192);
    }

    /**
     * @param $type
     * @return bool
     */
    public function typeMethodExists($type): bool
    {
        if(method_exists($this, $this->getMethod($type))){
            return true;
        }

        return (bool) $this->getPluginMethod($type);
    }

    /**
     * @param string $type
     * @return array|null [class, method]
     */
    public function getPluginMethod(string $type): ?array
    {
        $plugins = $this->websiteSettings->getPluginList();

        foreach ($plugins as $plugin) {
            if ( ! method_exists($plugin, 'getMediaResizeService')) {
                continue;
            }

            $resizeService = $this->di->get($plugin->getMediaResizeService());

            $method = 'resize' . $this->stringService->dashesToCamelCase($type, true);

            if (method_exists($resizeService, $method)) {
                return [$resizeService, $method];
            }
        }

        return null;
    }

    /**
     * @param $type
     * @return string
     */
    private function getMethod($type): string
    {
        return 'resize' . $this->stringService->dashesToCamelCase($type, true);
    }

    /**
     * @param string|null $type
     * @return ThumbSettings|null
     */
    public function getThumbSettings(?string $type): ?ThumbSettings
    {
        if ($type == 'thumb') {
            return null;
        }

        $settingsMethod = 'get' . $this->stringService->dashesToCamelCase($type, true) . 'Settings';

        if ( ! method_exists($this, $settingsMethod)) {
            return null;
        }

        return $this->$settingsMethod();
    }
}