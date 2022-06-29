<?php declare(strict_types=1);

namespace Website\ObjectList;

use KikCmsCore\Classes\ObjectMap;
use Website\Models\TestCompany;

class CompanyMap extends ObjectMap
{
    /**
     * @inheritdoc
     * @return TestCompany|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return TestCompany|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return TestCompany|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return TestCompany|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
