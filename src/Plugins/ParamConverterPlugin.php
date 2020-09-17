<?php declare(strict_types=1);

namespace KikCMS\Plugins;


use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Services\ModelService;
use KikCmsCore\Classes\Model;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
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
class ParamConverterPlugin extends Injectable
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

            if ( ! array_key_exists($this->getIdParamName($parameter), $paramValueMap)) {
                continue;
            }

            $paramValueMap = $this->replaceParameter($class, $parameter, $paramValueMap);
        }

        return $paramValueMap;
    }

    /**
     * @param ReflectionClass $class
     * @param ReflectionParameter $parameter
     * @param array $paramValueMap
     * @return array
     * @throws ObjectNotFoundException
     */
    private function replaceParameter(ReflectionClass $class, ReflectionParameter $parameter, array $paramValueMap)
    {
        $obParamName = $this->getParamName($parameter);
        $idParamName = $this->getIdParamName($parameter);

        $objectId = (int) $paramValueMap[$idParamName];

        $object = $this->modelService->getObject($class->getName(), $objectId);

        if ( ! $object && ! $parameter->allowsNull()) {
            throw new ObjectNotFoundException($obParamName . $objectId);
        }

        $paramValueMap = array_change_key($paramValueMap, $idParamName, $obParamName);

        $paramValueMap[$obParamName] = $object;

        return $paramValueMap;
    }

    /**
     * @param ReflectionParameter $parameter
     * @return string
     */
    private function getParamName(ReflectionParameter $parameter): string
    {
        return $parameter->getName();
    }

    /**
     * @param ReflectionParameter $parameter
     * @return string
     */
    private function getIdParamName(ReflectionParameter $parameter): string
    {
        return $this->getParamName($parameter) . 'Id';
    }
}