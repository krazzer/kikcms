<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;


use Exception;
use Phalcon\Mvc\View as PhalconView;

class View extends PhalconView
{
    /** @var array */
    private array $namespaces = [];

    /**
     * @inheritdoc
     */
    protected function engineRender($engines, $viewPath, $silence, $mustClean = true)
    {
        $viewPath = $this->convertNamespace($viewPath);

        return parent::engineRender($engines, $viewPath, $silence, $mustClean);
    }

    /**
     * @inheritdoc
     */
    public function exists($view): bool
    {
        if( ! $this->isNamespaced($view)){
            return parent::exists($view);
        }

        foreach ($this->registeredEngines as $extension => $engine) {
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
    public function pick($viewPath): PhalconView
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