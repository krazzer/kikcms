<?php

namespace KikCMS\Plugins;


use KikCMS\Services\ModelService;
use KikCmsCore\Classes\Model;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Converts Controller method parameters to automatically get the Object by it's id
 *
 * E.g: If the param 'Product $product' is present in the Controller method, and the route contains productId, it will
 * provide the method with the Product object for that id.
 *
 * @property ModelService $modelService
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

        if ( ! method_exists($dispatcher->getControllerClass(), $actionName)) {
            return;
        }

        $method = new ReflectionMethod($dispatcher->getControllerClass(), $actionName);

        $methodParameters = $method->getParameters();
        $paramValueMap    = $dispatcher->getParams();

        $replacedParamValueMap = $this->getConvertedParameters($methodParameters, $paramValueMap);

        $dispatcher->setParams($replacedParamValueMap);

        // prevent unused parameter warning
        $event->setType($event->getType());
    }

    /**
     * @param ReflectionParameter[] $methodParameters
     * @param array $paramValueMap
     * @return array
     * @throws ObjectNotFoundException
     */
    public function getConvertedParameters(array $methodParameters, array $paramValueMap)
    {
        foreach ($methodParameters as $parameter) {
            if ( ! $class = $parameter->getClass()) {
                continue;
            }

            if ( ! $class->isSubclassOf(Model::class)) {
                continue;
            }

            if ( ! array_key_exists($this->getIdParamName($class), $paramValueMap)) {
                continue;
            }

            $paramValueMap = $this->replaceParameter($class, $paramValueMap);
        }

        return $paramValueMap;
    }

    /**
     * @param ReflectionClass $class
     * @param array $paramValueMap
     * @return array
     * @throws ObjectNotFoundException
     */
    private function replaceParameter(ReflectionClass $class, array $paramValueMap)
    {
        $obParamName = $this->getObjectParamName($class);
        $idParamName = $this->getIdParamName($class);

        if ( ! $object = $this->modelService->getObject($class->getName(), $paramValueMap[$idParamName])) {
            throw new ObjectNotFoundException($obParamName);
        }

        $paramValueMap = array_change_key($paramValueMap, $idParamName, $obParamName);

        $paramValueMap[$obParamName] = $object;

        return $paramValueMap;
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