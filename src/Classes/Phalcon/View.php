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
    protected function engineRender($engines, $viewPath, $silence, $mustClean = true): void
    {
        $viewPath = $this->convertNamespace($viewPath);

        parent::engineRender($engines, $viewPath, $silence, $mustClean);
    }

    /**
     * @param string $view
     * @return bool
     */
    public function exists(string $view): bool
    {
        if( ! $this->isNamespaced($view)){
            return parent::has($view);
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
    public function setNamespaces(array $namespaces): void
    {
        $this->namespaces = $namespaces;
    }

    /**
     * @param mixed $renderView
     * @return PhalconView
     * @throws Exception
     */
    public function pick(mixed $renderView): PhalconView
    {
        if( ! $this->exists($renderView)){
            throw new Exception('View "' . $renderView . '" not found.');
        }

        return parent::pick($renderView);
    }

    /**
     * @param mixed $viewPath
     * @return mixed
     */
    private function convertNamespace(mixed $viewPath): mixed
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
        return str_starts_with($viewPath, '@');
    }
}