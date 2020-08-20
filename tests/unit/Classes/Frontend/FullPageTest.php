<?php /** @noinspection PhpUndefinedMethodInspection */

namespace unit\Classes\Frontend;

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
        $page->link = 1;
        $page->created_at = '2020-01-01';
        $pageLanguage = new PageLanguage();
        $pageLanguage->page_id = 1;
        $pageLanguage->language_code = 'en';
        $page->pageLanguageEn = $pageLanguage;

        $fullPage = new FullPage($page, $pageLanguage, ['field' => 'value'], 'url');

        $this->assertEquals('value', $fullPage->getField());
        $this->assertNull($fullPage->getNonExistingField());
        $this->assertEquals('testKey', $fullPage->getKey());
        $this->assertEquals('en', $fullPage->getLanguageCode());
        $this->assertEquals(1, $fullPage->getLink());
        $this->assertEquals(['field' => 'value'], $fullPage->getContent());
        $this->assertEquals('2020-01-01', $fullPage->getCreatedDate()->format('Y-m-d'));

        $fullPage->setContent([]);
        $fullPage->setPage($page);
        $fullPage->setPageLanguage($pageLanguage);
        $fullPage->setUrl('url');
    }
}
