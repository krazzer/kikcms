<?php

namespace KikCMS\Classes\Phalcon;

use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;

class SafeTagFactory extends TagFactory
{
    /**
     * Vangt het aanroepen van de factory als functie volledig in PHP af,
     * zonder parent::__invoke() aan te roepen om C-extension waarschuwingen te voorkomen.
     */
    public function __invoke(...$args)
    {
        if (!empty($args) && is_string($args[0])) {
            $method = array_shift($args);
            return $this->__call($method, $args);
        }

        throw new \BadMethodCallException("TagFactory kan niet als functie worden aangeroepen zonder geldige helpernaam.");
    }

    /**
     * Vangt dynamische tag-aanroepen op (bijv. $tag->inputText(...))
     */
    public function __call(string $method, array $args)
    {
        try {
            return parent::__call($method, $args);
        } catch (\Throwable $e) {
            return $this->resolveFallbackHelper($method, $args);
        }
    }

    /**
     * Vangt newInstance af met type-beveiliging tegen null-waardes
     */
    public function newInstance(string $name): object
    {
        if ($name === null) {
            throw new \InvalidArgumentException("Method newInstance verwacht een geldige string als element naam.");
        }

        try {
            return parent::newInstance($name);
        } catch (\Throwable $e) {
            return $this->resolveFallbackHelper($name, []);
        }
    }

    protected function resolveFallbackHelper(?string $name, array $args)
    {
        if ($name === null) {
            return null;
        }

        // $escaper = property_exists($this, 'escaper') ? $this->escaper : new Escaper();
        $escaper = new Escaper();

        $className = "\\Phalcon\\Html\\Helper\\" . ucfirst($name);
        if (class_exists($className)) {
            $helper = new $className($escaper);
            if (!empty($args)) {
                return $helper(...$args);
            }
            return $helper;
        }

        // Specifieke fallback voor input-elementen (zoals inputText)
        if (str_starts_with(strtolower($name), 'input')) {
            $genericClass = "\\Phalcon\\Html\\Helper\\Input\\Generic";
            if (class_exists($genericClass)) {
                $helper = new $genericClass($escaper);
                if (!empty($args)) {
                    return $helper(...$args);
                }
                return $helper;
            }
        }

        return null;
    }
}