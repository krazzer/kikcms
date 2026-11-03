<?php

namespace KikCMS\Classes\Phalcon;

use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;

class SafeTagFactory extends TagFactory
{
    /**
     * @param string $name
     * @return object
     */
    public function newInstance(string $name): object
    {
        if ( ! isset($this->factories[$name])) {
            throw new \Exception($name);
        }

        if ( ! isset($this->instances[$name])) {
            $factory = $this->factories[$name];

            $this->instances[$name] = @call_user_func($factory);
        }

        if ( ! $this->instances[$name]) {
            return $this->resolveFallbackHelper($name);
        }

        return $this->instances[$name];
    }

    /**
     * @param string $name
     * @return object
     */
    protected function resolveFallbackHelper(string $name): object
    {
        $escaper = new Escaper();

        $nameNoInput = strtolower(str_replace('input', '', $name));

        $className      = "\\Phalcon\\Html\\Helper\\" . ucfirst($name);
        $inputClassName = "\\Phalcon\\Html\\Helper\\Input\\" . ucfirst($nameNoInput);
        $genericClass   = "\\Phalcon\\Html\\Helper\\Input\\Generic";

        if (class_exists($className)) {
            return new $className($escaper);
        }

        if (class_exists($inputClassName)) {
            return new $inputClassName($escaper);
        }

        return new $genericClass($escaper, $this->newInstance('doctype'), $nameNoInput);
    }
}