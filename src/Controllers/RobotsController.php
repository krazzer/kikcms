<?php declare(strict_types=1);


namespace KikCMS\Controllers;


use DateTime;
use DateTimeZone;
use DOMDocument;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Models\PageLanguage;
use KikCMS\Services\Pages\UrlService;

/**
 * @property UrlService $urlService
 */
class RobotsController extends BaseController
{
    public function sitemapAction()
    {
        $expireDate = new DateTime();
        $expireDate->modify('+1 day');

        $this->response->setExpires($expireDate);

        $this->response->setHeader('Content-Type', "application/xml; charset=UTF-8");

        $sitemap = new DOMDocument("1.0", "UTF-8");

        $urlSet = $sitemap->createElement('urlset');
        $urlSet->setAttribute('xmlns', 'https://www.sitemaps.org/schemas/sitemap/0.9');
        $urlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $urlSet->setAttribute('xsi:schemaLocation', 'https://www.sitemaps.org/schemas/sitemap/0.9 https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

        $links = $this->urlService->getUrls();

        $modifiedAt = new DateTime();
        $modifiedAt->setTimezone(new DateTimeZone('UTC'));

        $comment = $sitemap->createComment(' Last update of sitemap ' . date("Y-m-d H:i:s") . ' ');

        $urlSet->appendChild($comment);

        $baseUri = trim($this->url->getBaseUri(), '/');

        foreach ($links as $link) {
            $url  = $sitemap->createElement('url');
            $href = $baseUri . $link;

            $url->appendChild($sitemap->createElement('loc', $href));
            $url->appendChild($sitemap->createElement('changefreq', 'daily'));
            $url->appendChild($sitemap->createElement('priority', '0.5'));

            $urlSet->appendChild($url);
        }

        $sitemap->appendChild($urlSet);

        $this->response->setContent($sitemap->saveXML());

        return $this->response;
    }

    /**
     * @return string
     */
    public function robotsAction(): string
    {
        $this->response->setHeader('Content-type', 'text/plain');

        return $this->view->getPartial('frontend/robots', [
            'sitemapUrl' => $this->url->getBaseUri() . 'sitemap.xml'
        ]);
    }
}