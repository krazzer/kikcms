<?php

namespace KikCMS\Classes\Finder;


use Phalcon\Di\Injectable;
use Phalcon\Http\Request\File;

/**
 * @property FinderFileService $finderFileService
 */
class Finder extends Injectable
{
    /**
     * @return string
     */
    public function render()
    {
        $this->addAssets();

        return $this->renderView('index', [
            'instance' => $this->getInstance(),
        ]);
    }

    /**
     * Renders a view
     *
     * @param $viewName
     * @param array $parameters
     *
     * @return string
     */
    public function renderView($viewName, array $parameters = []): string
    {
        return $this->view->getPartial('finder/' . $viewName, $parameters);
    }

    /**
     * Creates an unique id for the finder js class so multiple instances don't get mixed up
     *
     * @return string
     */
    private function getInstance()
    {
        return uniqid('finder');
    }

    /**
     * Adds html/css required for finder
     */
    private function addAssets()
    {
        $this->view->assets->addCss('cmsassets/css/toolbarComponent.css');
        $this->view->assets->addCss('cmsassets/css/finder.css');

        $this->view->assets->addJs('cmsassets/js/finder/finder.js');
    }

    /**
     * @param File[] $files
     */
    public function uploadFiles(array $files)
    {
        //todo: add devense
        foreach ($files as $file) {
            $this->finderFileService->create($file);
        }
    }
}