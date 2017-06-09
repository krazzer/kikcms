<?php

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;

/**
 * Contains methods to resize thumbnails in predefined formats
 */
class MediaResizeBase extends WebsiteExtendable
{
    /**
     * @param string|null $type
     * @return bool
     */
    public function typeExists(string $type = null): bool
    {
        if( ! $type){
            return true;
        }

        return $this->typeMethodExists($type);
    }

    public function resizeByType(string $type)
    {
        if( ! $this->typeMethodExists($type)){
            $this->throwMethodDoesNotExistException($this->getMethod($type));
        }

        $method = $this->getMethod($type);

        return $this->$method();
    }

    /**
     * @param $type
     * @return bool
     */
    private function typeMethodExists($type): bool
    {
        return method_exists($this, $this->getMethod($type));
    }

    /**
     * @param $type
     * @return string
     */
    private function getMethod($type): string
    {
        return 'resize' . ucfirst($type);
    }
}