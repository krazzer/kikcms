<?php declare(strict_types=1);

namespace Website\ObjectList;

use KikCmsCore\Classes\ObjectList;
use Website\Models\PersonInterest;

class PersonInterestList extends ObjectList
{
    /**
     * @inheritdoc
     * @return PersonInterest|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return PersonInterest|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return PersonInterest|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return PersonInterest|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
