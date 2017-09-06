<?php

namespace KikCMS\Classes\Phalcon;


use Exception;
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
        $viewPath = $this->convertNamespace($viewPath);

        return parent::_engineRender($engines, $viewPath, $silence, $mustClean, $cache);
    }

    /**
     * @inheritdoc
     */
    public function exists($view)
    {
        if( ! $this->isNamespaced($view)){
            return parent::exists($view);
        }

        foreach ($this->_registeredEngines as $extension => $engine) {
            if(file_exists($this->convertNamespace($view)) . $extension){
                return true;
            }
        }

        return false;
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

    /**
     * @param mixed $viewPath
     * @return PhalconView
     * @throws Exception
     */
    public function pick($viewPath)
    {
        if( ! $this->exists($viewPath)){
            throw new Exception('View "' . $viewPath . '" not found.');
        }

        return parent::pick($viewPath);
    }

    /**
     * @param mixed $viewPath
     * @return mixed
     */
    private function convertNamespace($viewPath)
    {
        if ($this->isNamespaced($viewPath)) {
            foreach ($this->namespaces as $namespace => $path) {
                $viewPath = str_replace('@' . $namespace . '/', $path, $viewPath);
            }
        }

        return $viewPath;
    }

    private function isNamespaced($viewPath): bool
    {
        return strpos($viewPath, '@') === 0;
    }
}