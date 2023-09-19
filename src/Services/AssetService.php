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
        $dontStartWithList = ['//', 'https://', 'ht=tp://'];

        foreach ($dontStartWithList as $dontStartWith) {
            if (str_startswith($file, $dontStartWith)) {
                return $file;
            }
        }

        $publicFolder = $this->config->application->path . $this->config->application->publicFolder;
        $filePath     = $publicFolder . DIRECTORY_SEPARATOR . $file;

        if ($this->config->isDev()) {
            $devFilePath = $publicFolder . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . $file;

            if(file_exists($devFilePath)){
                $filePath = $devFilePath;
                $file = 'build' . DIRECTORY_SEPARATOR . $file;
            }
        }

        return $file . '?v=' . filemtime($filePath);
    }

    /**
     * @param string $file
     */
    public function addCss(string $file): void
    {
        $file = $this->addVersion($file);

        $this->view->assets->addCss($file);
    }

    /**
     * @param string $file
     * @param bool $local
     */
    public function addJs(string $file, bool $local = true): void
    {
        $file = $this->addVersion($file);

        $this->view->assets->addJs($file, $local);
    }
}