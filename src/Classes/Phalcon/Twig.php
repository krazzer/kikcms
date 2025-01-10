<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;

use DateTime;
use DOMDocument;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Services\TwigService;
use KikCMS\Services\Util\DateTimeService;
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
    public function getView(): View|ViewBaseInterface
    {
        return parent::getView();
    }

    /**
     * @param string $path
     * @param mixed $params
     * @param bool $mustClean
     */
    public function render(string $path, $params, bool $mustClean = false): void
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
    protected function registryFunctions(DiInterface $di): void
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

        // add truncate HTML filter
        $this->twig->addFilter(new TwigFilter('truncate_html', function ($string, int $length = 50) use ($di) {
            if(mb_strlen($string) < $length){
                return $string;
            }

            $truncated = mb_substr($string, 0, $length);

            if (mb_strlen($string) > $length) {
                $truncated .= '...';
            }

            $doc = new DOMDocument();
            libxml_use_internal_errors(true); // Verberg waarschuwingen van ongeldige HTML
            $doc->loadHTML('<?xml encoding="UTF-8">' . $truncated, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $body = $doc->getElementsByTagName('body')->item(0);

            $newHtml = '';

            if ($body) {
                foreach ($body->childNodes as $child) {
                    $newHtml .= $doc->saveHTML($child);
                }
            } else {
                foreach ($doc->childNodes as $child) {
                    $newHtml .= $doc->saveHTML($child);
                }
            }

            return $newHtml;
        }));

        // add ucfirst filter
        $this->twig->addFilter(new TwigFilter('ucfirst', 'ucfirst'));

        // add lcfirst filter
        $this->twig->addFilter(new TwigFilter('lcfirst', 'lcfirst'));

        // add json_decode filter
        $this->twig->addFilter(new TwigFilter('json_decode', 'json_decode'));

        // add price filter
        $this->twig->addFilter(new TwigFilter('price', function ($price, $decimals = 2) use ($di) {
            return $di->getShared("numberService")->getPriceFormat((float) $price, $decimals);
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

            /** @var DateTimeService $dateTimeService */
            $dateTimeService = $this->getDI()->get('dateTimeService');

            return $dateTimeService->format($format, $dateTime);
        }));

        /** @var WebsiteSettingsBase $siteSettings */
        $siteSettings = $di->getShared('websiteSettings');

        $siteSettings->addTwigFunctions($this->twig);
    }
}
