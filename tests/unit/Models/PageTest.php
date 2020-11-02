<?php

namespace unit\Models;

use Helpers\Unit;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;

class PageTest extends Unit
{
    public function testChangingParent()
    {
        $this->getDbDi();
        $this->addDefaultLanguage();

        $page1 = new Page();
        $page1->id = 1;
        $page1->display_order = 1;
        $page1->lft = 1;
        $page1->rgt = 2;
        $page1->setPreventNestedSetUpdate(true);
        $page1->save();

        $page2 = new Page();
        $page2->id = 2;
        $page2->lft = 3;
        $page2->rgt = 4;
        $page2->display_order = 1;
        $page2->setPreventNestedSetUpdate(true);
        $page2->save();

        $subPage1 = new Page();
        $subPage1->id = 3;
        $subPage1->display_order = 1;
        $subPage1->setParentId(1);
        $subPage1->setPreventNestedSetUpdate(true);
        $subPage1->save();

        $subPage2 = new Page();
        $subPage2->id = 4;
        $subPage2->display_order = 1;
        $subPage2->setParentId(2);
        $subPage2->setPreventNestedSetUpdate(true);
        $subPage2->save();

        $this->assertEquals(1, $subPage2->getDisplayOrder());

        $subPage2 = Page::getById(4);
        $subPage2->setParentId(1);
        $subPage2->setPreventNestedSetUpdate(true);
        $subPage2->save();

        $subPage2 = Page::getById(4);

        // test if display order has been reset, because the parent has changed
        $this->assertEquals(2, $subPage2->getDisplayOrder());

        $subPage2->setParentId(1);
        $subPage2->setPreventNestedSetUpdate(true);
        $subPage2->save();

        // test if display order stays the same, since the parent is the same
        $this->assertEquals(2, $subPage2->getDisplayOrder());
    }

    public function testGetParentPageLanguageWithSlugByLangCode()
    {
        $this->getDbDi();
        $this->addDefaultLanguage();

        $page = new Page();

        // no parent, so return null
        $this->assertNull($page->getParentPageLanguageWithSlugByLangCode('en'));

        // has parent, but parent has no pagelanguage, so null
        $parentPage = new Page();
        $page->parent = $parentPage;

        $this->assertNull($page->getParentPageLanguageWithSlugByLangCode('en'));

        // has parent with pagelanguage, but it has no slug, so null
        $parentPage = new Page();

        $parentPagePageLanguage = new PageLanguage();
        $parentPagePageLanguage->language_code = 'en';
        $parentPage->pageLanguageEn = $parentPagePageLanguage;

        $page->parent = $parentPage;

        $this->assertNull($page->getParentPageLanguageWithSlugByLangCode('en'));

        // has parent with pagelanguage, with slug, so success
        $parentPage = new Page();

        $parentPagePageLanguage = new PageLanguage();
        $parentPagePageLanguage->language_code = 'en';
        $parentPagePageLanguage->setSlug('slug');
        $parentPage->pageLanguageEn = $parentPagePageLanguage;

        $page->parent = $parentPage;

        $this->assertInstanceOf(PageLanguage::class, $page->getParentPageLanguageWithSlugByLangCode('en'));

        // has grandparent with pagelanguage, with slug, so success
        $parentPage = new Page();
        $grandParentPage = new Page();

        $parentPagePageLanguage = new PageLanguage();
        $parentPagePageLanguage->language_code = 'en';
        $parentPagePageLanguage->setSlug('slug');
        $grandParentPage->pageLanguageEn = $parentPagePageLanguage;

        $parentPage->parent = $grandParentPage;
        $page->parent = $parentPage;

        $this->assertInstanceOf(PageLanguage::class, $page->getParentPageLanguageWithSlugByLangCode('en'));
    }
}
