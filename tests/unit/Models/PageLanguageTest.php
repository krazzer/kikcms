<?php
declare(strict_types=1);

namespace KikCMS\Models;


use Helpers\Unit;

class PageLanguageTest extends Unit
{
    public function testBeforeSave()
    {
        $this->getDbDi();

        $pageLanguage = new PageLanguage();
        $pageLanguage->setSlug('slug');

        // has slug, so does nothing
        $result = $pageLanguage->beforeSave();

        $this->assertNull($result);
    }
}
