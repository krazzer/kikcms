<?php declare(strict_types=1);

namespace Website\ObjectList;

use KikCmsCore\Classes\ObjectMap;
use Website\Models\WorkDiscipline;

class WorkDisciplineMap extends ObjectMap
{
    /**
     * @inheritdoc
     * @return WorkDiscipline|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return WorkDiscipline|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return WorkDiscipline|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return WorkDiscipline|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
