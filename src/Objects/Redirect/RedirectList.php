<?php declare(strict_types=1);

namespace KikCMS\Objects\Redirect;

use KikCmsCore\Classes\ObjectList;

class RedirectList extends ObjectList
{
    /**
     * @inheritdoc
     * @return Redirect|false
     */
    public function current()
    {
        return parent::current();
    }

    /**
     * @inheritdoc
     * @return Redirect|false
     */
    public function get($key)
    {
        return parent::get($key);
    }

    /**
     * @inheritdoc
     * @return Redirect|false
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @inheritdoc
     * @return Redirect|false
     */
    public function getLast()
    {
        return parent::getLast();
    }
}
