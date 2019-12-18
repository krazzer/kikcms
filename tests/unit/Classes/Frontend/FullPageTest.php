<?php

namespace Classes\Frontend;

use Helpers\Unit;
use KikCMS\Classes\Frontend\FullPage;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;

class FullPageTest extends Unit
{
    public function test()
    {
        $this->getDbDi();

        $page = new Page();
        $page->key = 'testKey';
        $pageLanguage = new PageLanguage();
        $pageLanguage->page_id = 1;
        $page->pageLanguageEn = $pageLanguage;

        $fullPage = new FullPage($page, $pageLanguage, ['field' => 'value'], '/url');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals('value', $fullPage->getField());

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertNull($fullPage->getNonExistingField());

        $this->assertEquals('testKey', $fullPage->getKey());
    }
}
