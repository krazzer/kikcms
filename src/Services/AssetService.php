<?php declare(strict_types=1);


namespace KikCMS\Services;


use KikCMS\Classes\Phalcon\Injectable;

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
     * @param bool $local
     */
    public function addJs(string $file, bool $local = true)
    {
        $file = $this->addVersion($file);

        $this->view->assets->addJs($file, $local);
    }
}