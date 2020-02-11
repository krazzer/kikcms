<?php
declare(strict_types=1);

namespace functional;

use FunctionalTester;

class RobotsCest
{
    public function sitemapWorks(FunctionalTester $I)
    {
        $I->amOnPage('/sitemap.xml');
        $I->see('https://kikcmstest.dev/pagina-2');
    }

    public function robotsWorks(FunctionalTester $I)
    {
        $I->amOnPage('/robots.txt');
        $I->see('User-agent: *');
    }
}