<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;

use DateTime;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Services\TwigService;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\View\Engine;
use Phalcon\Mvc\ViewBaseInterface;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class Twig
 * @package Phalcon\Mvc\View\Engine
 */
class Twig extends Engine\AbstractEngine
{
    const DEFAULT_EXTENSION = '.twig';

    /** @var Environment */
    protected Environment $twig;

    /**
     * @param mixed|ViewBaseInterface $view
     * @param mixed|DiInterface $di
     * @param array $options
     * @param array $paths
     */
    public function __construct($view, $di, array $options = [], array $paths = [])
    {
        parent::__construct($view, $di);

        $loader = new FilesystemLoader($this->getView()->getViewsDir());

        foreach ($paths as $namespace => $path) {
            $loader->addPath($path, $namespace);
        }

        $this->twig = new Environment($loader, $options);

        if ($this->twig->isDebug()) {
            $this->twig->addExtension(new DebugExtension());
        }

        $this->registryFunctions($di);
    }

    /**
     * @return View|ViewBaseInterface
     */
    public function getView(): ViewBaseInterface
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
     * @param DiInterface $di
     */
    protected function registryFunctions(DiInterface $di)
    {
        $options = ['is_safe' => ['html']];

        $functions = [
            'allowed', 'config', 'css', 'endForm', 'file', 'fileBg', 'form', 'js', 'pageUrl', 'submitButton', 'svg',
            'tl', 'ucfirst', 'url', 'mediaFileBg', 'mediaFile'
        ];

        /** @var TwigService $twigService */
        $twigService = $di->get('twigService');

        foreach ($functions as $function) {
            $this->twig->addFunction(new TwigFunction($function, [$twigService, $function], $options));
        }

        // add truncate filter
        $this->twig->addFilter(new TwigFilter('truncate', function ($string, int $maxLength = 50) use ($di) {
            return $di->getShared("stringService")->truncate((string) $string, $maxLength);
        }));

        // add ucfirst filter
        $this->twig->addFilter(new TwigFilter('ucfirst', 'ucfirst'));

        // add price filter
        $this->twig->addFilter(new TwigFilter('price', function ($price) use ($di) {
            return $di->getShared("numberService")->getPriceFormat((float) $price);
        }));

        // add date filter
        $this->twig->addFilter(new TwigFilter('date', function ($dateTime, string $format = null) use ($di) {
            if( ! $dateTime){
                return '';
            }

            if ( ! $dateTime instanceOf DateTime) {
                $dateTime = new DateTime($dateTime);
            }

            $format = $format ?: $di->getShared('translator')->tl('system.dateDisplayFormat');
            return strftime($format, $dateTime->getTimestamp());
        }));

        /** @var WebsiteSettingsBase $siteSettings */
        $siteSettings = $di->getShared('websiteSettings');

        $siteSettings->addTwigFunctions($this->twig);
    }
}
