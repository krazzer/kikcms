<?php declare(strict_types=1);

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;
use KikCMS\Services\Util\StringService;
use Phalcon\Image\Adapter\AbstractAdapter;

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
     */
    public function crop(AbstractAdapter $image, $width, $height): void
    {
        $sourceWidth = $image->getWidth();
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

        $x0 = (int) (($newWidth - $width) / 2);
        $y0 = (int) (($newHeight - $height) / 2);

        if($newWidth != $sourceWidth || $newHeight != $sourceHeight){
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
        $method = $this->getMethod($type);
        $this->$method($image);
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
        return method_exists($this, $this->getMethod($type));
    }

    /**
     * @param $type
     * @return string
     */
    private function getMethod($type): string
    {
        return 'resize' . $this->stringService->dashesToCamelCase($type, true);
    }
}