<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;

use Phalcon\DiInterface;
use Phalcon\Mvc\Router;

/**
 * Adds some convenience to Phalcons UrlProvider
 */
class Url extends \Phalcon\Mvc\Url
{
    /** @var Router */
    protected $_router;

    /** @var DiInterface */
    protected $_dependencyInjector;

    /**
     * @inheritdoc
     */
    public function get($uri = null, $args = null, $local = null, $baseUri = null)
    {
        // initialize the router object if this is not done already
        if ( ! is_object($this->_router)) {
            $this->_router = $this->_dependencyInjector->getShared('router');
        }

        // transforms parameters
        if ($uri != null && is_string($uri) && $route = $this->_router->getRouteByName($uri)) {
            $args = $this->convertArguments($args, $route->getPaths());

            $routeName  = $uri;
            $uri        = $args;
            $uri['for'] = $routeName;
            $args       = null;
        }

        $uri = $this->fixDoubleSlashes($uri);

        return parent::get($uri, $args, $local, $baseUri);
    }

    /**
     * @param mixed $args
     * @param array $routePaths
     * @return array
     */
    public function convertArguments($args, array $routePaths): array
    {
        if( ! $args){
            return [];
        }

        // if the args are scalar, we will use the first param
        if (is_scalar($args)) {
            $firstParam = array_search(1, $routePaths);
            return [$firstParam => $args];
        }

        $newArgs = [];

        foreach ($args as $key => $value){
            if(is_numeric($key)){
                $newArgs[array_search($key + 1, $routePaths)] = $value;
            } else {
                $newArgs[$key] = $value;
            }
        }

        return $newArgs;
    }

    /**
     * Remove leading slash to prevent double slashes
     * This is a bug in Phalcon that should be corrected, added issue #13495
     *
     * @param mixed $uri
     * @return mixed
     */
    public function fixDoubleSlashes($uri)
    {
        if(is_string($uri) && substr($uri, 0, 1) == '/' && substr($uri, 1, 1) != '/'){
            $uri = substr($uri, 1);
        }

        return $uri;
    }
}