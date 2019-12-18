<?php

namespace Models;

use Helpers\Unit;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;

class PageTest extends Unit
{
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
