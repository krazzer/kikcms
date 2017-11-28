<?php

namespace KikCMS\Plugins;


use KikCmsCore\Classes\Model;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;
use ReflectionClass;
use ReflectionMethod;

/**
 * Converts Controller method parameters to automatically get the Object by it's id
 *
 * E.g: If the param 'Product $product' is present in the Controller method, and the route contains productId, it will
 * provide the method with the Product object for that id.
 */
class ParamConverterPlugin extends Plugin
{
    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @throws NotFoundException
     */
    public function beforeDispatchLoop(Event $event, Dispatcher $dispatcher)
    {
        $actionName = $dispatcher->getActionName() . 'Action';

        if ( ! class_exists($dispatcher->getControllerClass())) {
            return;
        }

        if( ! method_exists($dispatcher->getControllerClass(), $actionName)){
            return;
        }

        $method = new ReflectionMethod($dispatcher->getControllerClass(), $actionName);

        $parameters    = $method->getParameters();
        $paramValueMap = $dispatcher->getParams();

        foreach ($parameters as $parameter) {
            if ( ! $class = $parameter->getClass()) {
                continue;
            }

            if ( ! $class->isSubclassOf(Model::class)) {
                continue;
            }

            if ( ! array_key_exists($this->getIdParamName($class), $paramValueMap)) {
                continue;
            }

            $this->replaceParameter($class, $dispatcher);
        }

        // prevent unused parameter warning
        $event->setType($event->getType());
    }

    /**
     * Replaces the key of given map with a new key at the same position, with also a new value
     *
     * @param array $map
     * @param string $oldKey
     * @param string $newKey
     * @param Model $object
     * @return array
     */
    private function replaceKeyAndValue(array $map, string $oldKey, string $newKey, Model $object): array
    {
        $keys = array_keys($map);

        $keys[array_search($oldKey, $keys)] = $newKey;

        $map = array_combine($keys, $map);

        $map[$newKey] = $object;

        return $map;
    }

    /**
     * @param ReflectionClass $class
     * @param Dispatcher $dispatcher
     * @throws ObjectNotFoundException
     */
    private function replaceParameter(ReflectionClass $class, Dispatcher $dispatcher)
    {
        $paramValueMap = $dispatcher->getParams();

        $obParamName = $this->getObjectParamName($class);
        $idParamName = $this->getIdParamName($class);

        $object = $class->newInstance()::getById($paramValueMap[$idParamName]);

        if ( ! $object) {
            throw new ObjectNotFoundException();
        }

        $newParameters = $this->replaceKeyAndValue($paramValueMap, $idParamName, $obParamName, $object);

        $dispatcher->setParams($newParameters);
    }

    /**
     * @param ReflectionClass $class
     * @return string
     */
    private function getObjectParamName(ReflectionClass $class): string
    {
        return lcfirst($class->getShortName());
    }

    /**
     * @param ReflectionClass $class
     * @return string
     */
    private function getIdParamName(ReflectionClass $class): string
    {
        return $this->getObjectParamName($class) . 'Id';
    }
}