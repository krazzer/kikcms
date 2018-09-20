<?php

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
            // if the args are scalar, we will use the first param
            if (is_scalar($args)) {
                $firstParam = array_search(1, $route->getPaths());
                $args       = [$firstParam => $args];
            }

            $routeName  = $uri;
            $uri        = $args;
            $uri['for'] = $routeName;
            $args       = null;
        }

        // remove leading slash to prevent double slashes
        // this is a bug in Phalcon that should be corrected, added issue #13495
        if(is_string($uri) && substr($uri, 0, 1) == '/' && substr($uri, 1, 1) != '/'){
            $uri = substr($uri, 1);
        }

        return parent::get($uri, $args, $local, $baseUri);
    }
}