<?php

namespace KikCMS\Classes\Phalcon;


use Phalcon\Cache\BackendInterface;
use Phalcon\Mvc\View as PhalconView;

class View extends PhalconView
{
    /** @var array */
    private $namespaces = [];

    /**
     * @inheritdoc
     */
    protected function _engineRender($engines, $viewPath, $silence, $mustClean, BackendInterface $cache = null)
    {
        // convert @namespace to full path so that Phalcon accepts it
        if (strpos($viewPath, '@') === 0) {
            foreach ($this->namespaces as $namespace => $path) {
                $viewPath = str_replace('@' . $namespace . '/', $path, $viewPath);
            }
        }

        return parent::_engineRender($engines, $viewPath, $silence, $mustClean, $cache);
    }

    /**
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * @param array $namespaces
     */
    public function setNamespaces(array $namespaces)
    {
        $this->namespaces = $namespaces;
    }
}