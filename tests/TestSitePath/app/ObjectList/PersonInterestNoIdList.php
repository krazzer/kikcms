<?php declare(strict_types=1);

namespace Website\ObjectList;

use KikCmsCore\Classes\ObjectList;
use Website\Models\PersonInterestNoId;

class PersonInterestNoIdList extends ObjectList
{
    /**
     * @inheritdoc
     * @return PersonInterestNoId|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return PersonInterestNoId|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return PersonInterestNoId|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return PersonInterestNoId|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
