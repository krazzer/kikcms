<?php

namespace KikCMS\Classes\ObjectStorage;

use KikCMS\Config\FinderConfig;
use KikCMS\Config\MimeConfig;

class ThumbSettings
{
    /** @var bool set true to automatically double the given width & height for retina monitors */
    private bool $x2;

    /** @var string */
    private string $resizeType;

    /** @var string|null */
    private ?string $extension;

    /** @var int */
    private int $width;

    /** @var int */
    private int $height;

    /** @var int */
    private int $quality;

    /**
     * @param int $width
     * @param int $height
     * @param string $resizeType
     * @param string|null $extension
     * @param int $quality
     * @param bool $x2
     */
    public function __construct(int $width, int $height, string $resizeType = FinderConfig::THUMB_RESIZE_TYPE_RESIZE,
        ?string $extension = MimeConfig::WEBP, int $quality = 60, bool $x2 = true)
    {
        $this->width      = $width;
        $this->height     = $height;
        $this->resizeType = $resizeType;
        $this->extension  = $extension;
        $this->x2         = $x2;
        $this->quality = $quality;
    }

    /**
     * @return bool
     */
    public function isX2(): bool
    {
        return $this->x2;
    }

    /**
     * @param bool $x2
     * @return $this
     */
    public function setX2(bool $x2): ThumbSettings
    {
        $this->x2 = $x2;
        return $this;
    }

    /**
     * @return string
     */
    public function getResizeType(): string
    {
        return $this->resizeType;
    }

    /**
     * @param string $resizeType
     * @return $this
     */
    public function setResizeType(string $resizeType): ThumbSettings
    {
        $this->resizeType = $resizeType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @param string|null $extension
     * @return $this
     */
    public function setExtension(?string $extension): ThumbSettings
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth(int $width): ThumbSettings
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight(int $height): ThumbSettings
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * @param int $quality
     * @return $this
     */
    public function setQuality(int $quality): ThumbSettings
    {
        $this->quality = $quality;
        return $this;
    }
}