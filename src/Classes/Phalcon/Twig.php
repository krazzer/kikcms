<?php

namespace KikCMS\Classes\Phalcon;

use DateTime;
use Phalcon\Config;
use Phalcon\DiInterface;
use Phalcon\Mvc\View\Engine;
use Phalcon\Mvc\View\EngineInterface;
use Phalcon\Mvc\ViewBaseInterface;
use Phalcon\Tag;

/**
 * Class Twig
 * @package Phalcon\Mvc\View\Engine
 */
class Twig extends Engine implements EngineInterface
{
    const DEFAULT_EXTENSION = '.twig';

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @param mixed|\Phalcon\Mvc\ViewBaseInterface $view
     * @param mixed|\Phalcon\DiInterface $dependencyInjector
     * @param array $options
     * @param array $paths
     */
    public function __construct($view, $dependencyInjector, array $options = [], array $paths = [])
    {
        parent::__construct($view, $dependencyInjector);

        $loader = new \Twig_Loader_Filesystem($this->getView()->getViewsDir());

        foreach ($paths as $namespace => $path) {
            $loader->addPath($path, $namespace);
        }

        $this->twig = new \Twig_Environment($loader, $options);

        if ($this->twig->isDebug()) {
            $this->twig->addExtension(new \Twig_Extension_Debug());
        }

        $this->registryFunctions($view, $dependencyInjector);
    }

    /**
     * @return View|ViewBaseInterface
     */
    public function getView()
    {
        return parent::getView();
    }

    /**
     * @param string $path
     * @param mixed $params
     * @param bool $mustClean
     */
    public function render($path, $params, $mustClean = false)
    {
        if ( ! $params) {
            $params = [];
        }

        // convert full paths back to the @namespace notation twig understands
        foreach ($this->getView()->getNamespaces() as $namespace => $namespacePath) {
            $path = str_replace($namespacePath, '@' . $namespace . '/', $path);
        }

        $view    = str_replace($this->getView()->getViewsDir(), '', $path);
        $content = $this->twig->render($view, $params);

        if ($mustClean) {
            $this->getView()->setContent($content);
            return;
        }

        echo $content;
    }

    /**
     * Registers common function in Twig
     *
     * @param \Phalcon\Mvc\ViewInterface $view
     * @param \Phalcon\DiInterface $di
     * @param array $userFunctions
     */
    protected function registryFunctions($view, DiInterface $di, $userFunctions = [])
    {
        $options = ['is_safe' => ['html']];

        $functions = [
            new \Twig_SimpleFunction('allowed', function ($resourceName, $access = '*', array $parameters = null) use ($di) {
                return $di->get("acl")->allowed($resourceName, $access, $parameters);
            }, $options),
            new \Twig_SimpleFunction('content', function () use ($view) {
                return $view->getContent();
            }, $options),
            new \Twig_SimpleFunction('partial', function ($partialPath) use ($view) {
                return $view->partial($partialPath);
            }, $options),
            new \Twig_SimpleFunction('linkTo', function ($parameters, $text = null) {
                return Tag::linkTo($parameters, $text);
            }, $options),
            new \Twig_SimpleFunction('textField', function ($parameters) {
                return Tag::textField($parameters);
            }, $options),
            new \Twig_SimpleFunction('passwordField', function ($parameters) {
                return Tag::passwordField($parameters);
            }, $options),
            new \Twig_SimpleFunction('hiddenField', function ($parameters) {
                return Tag::hiddenField($parameters);
            }, $options),
            new \Twig_SimpleFunction('fileField', function ($parameters) {
                return Tag::fileField($parameters);
            }, $options),
            new \Twig_SimpleFunction('checkField', function ($parameters) {
                return Tag::checkField($parameters);
            }, $options),
            new \Twig_SimpleFunction('radioField', function ($parameters) {
                return Tag::radioField($parameters);
            }, $options),
            new \Twig_SimpleFunction('submitButton', function ($parameters, $data) {
                return Tag::submitButton(['value' => $parameters] + $data);
            }, $options),
            new \Twig_SimpleFunction('selectStatic', function ($parameters, $data = []) {
                return Tag::selectStatic($parameters, $data);
            }, $options),
            new \Twig_SimpleFunction('select', function ($parameters, $data = []) {
                return Tag::select($parameters, $data);
            }, $options),
            new \Twig_SimpleFunction('textArea', function ($parameters) {
                return Tag::textArea($parameters);
            }, $options),
            new \Twig_SimpleFunction('form', function ($parameters = []) {
                return Tag::form($parameters);
            }, $options),
            new \Twig_SimpleFunction('endForm', function () {
                return Tag::endForm();
            }, $options),
            new \Twig_SimpleFunction('getTitle', function () {
                return Tag::getTitle();
            }, $options),
            new \Twig_SimpleFunction('stylesheetLink', function (string $url, bool $local = true) {
                $parameters = [$url, $local, 'nonce' => $this->view->cspNonce];
                return Tag::stylesheetLink($parameters);
            }, $options),
            new \Twig_SimpleFunction('javascriptInclude', function (string $url, bool $local = true) {
                $parameters = [$url, $local, 'nonce' => $this->view->cspNonce];
                return Tag::javascriptInclude($parameters);
            }, $options),
            new \Twig_SimpleFunction('image', function ($parameters) {
                return Tag::image($parameters);
            }, $options),
            new \Twig_SimpleFunction('friendlyTitle', function ($text, $separator = null, $lowercase = true) {
                return Tag::friendlyTitle($text, $separator, $lowercase);
            }, $options),
            new \Twig_SimpleFunction('getDocType', function () {
                return Tag::getDocType();
            }, $options),
            new \Twig_SimpleFunction('getSecurityToken', function () use ($di) {
                return $di->get("security")->getToken();
            }, $options),
            new \Twig_SimpleFunction('getSecurityTokenKey', function () use ($di) {
                return $di->get("security")->getTokenKey();
            }, $options),
            new \Twig_SimpleFunction('url', function ($route, $args = null) use ($di) {
                return $di->get("url")->get($route, $args);
            }, $options),
            new \Twig_SimpleFunction('route', function ($route, $args = null) use ($di) {
                return $di->get("url")->get($route, $args);
            }, $options),
            new \Twig_SimpleFunction('tl', function ($string, $parameters = []) use ($di) {
                return $di->getShared("translator")->tl($string, $parameters);
            }, $options),
            new \Twig_SimpleFunction('config', function ($string) use ($di) {
                return $this->getConfig($string, $di);
            }),
            new \Twig_SimpleFunction('ucfirst', function ($string) {
                return ucfirst($string);
            }, $options),
        ];

        if ( ! empty($userFunctions)) {
            $functions = array_merge($functions, $userFunctions);
        }

        foreach ($functions as $function) {
            $this->twig->addFunction($function);
        }

        $this->twig->addFilter(new \Twig_SimpleFilter('truncate', function ($string, int $maxLength = 50) use ($di) {
            return $di->getShared("stringService")->truncate((string) $string, $maxLength);
        }));

        $this->twig->addFilter(new \Twig_SimpleFilter('price', function ($price) use ($di) {
            return $di->getShared("numberService")->getPriceFormat((float) $price);
        }));

        $this->twig->addFilter(new \Twig_SimpleFilter('date', function ($dateTime, string $format = null) use ($di) {
            if( ! $dateTime instanceOf DateTime){
                $dateTime = new DateTime($dateTime);
            }

            $format = $format ?: $di->getShared('translator')->tl('system.dateDisplayFormat');
            return strftime($format, $dateTime->getTimestamp());
        }));
    }

    /**
     * @param string $string
     * @param DiInterface $di
     * @return null|string
     */
    private function getConfig(string $string, DiInterface $di): ?string
    {
        $string = explode('.', $string);

        /** @var Config $configGroup */
        $configGroup = $di->get("config")->get($string[0]);

        if ( ! $configGroup) {
            return null;
        }

        return $configGroup->get($string[1]);
    }
}
