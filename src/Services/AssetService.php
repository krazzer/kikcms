<?php


namespace KikCMS\Services;


use Phalcon\Di\Injectable;

/**
 * @property AssetService $assetService
 */
class AssetService extends Injectable
{
    /**
     * @param string $file
     * @return string
     */
    public function addVersion(string $file): string
    {
        $dontStartWithList = ['//', 'https://', 'http://'];

        foreach ($dontStartWithList as $dontStartWith){
            if(str_startswith($file, $dontStartWith)){
                return $file;
            }
        }

        $publicFolder = $this->config->application->path . $this->config->application->publicFolder;

        return $file . '?v=' . filemtime($publicFolder . DIRECTORY_SEPARATOR . $file);
    }

    /**
     * @param string $file
     */
    public function addCss(string $file)
    {
        $file = $this->addVersion($file);

        $this->view->assets->addCss($file);
    }

    /**
     * @param string $file
     */
    public function addJs(string $file)
    {
        $file = $this->addVersion($file);

        $this->view->assets->addJs($file);
    }
}