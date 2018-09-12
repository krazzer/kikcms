<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;

/**
 * Saves a value in another table, where the other table relates to the main table's primary id
 * @deprecated Use RelationKeys instead
 */
class OneToOne extends FieldStorage
{
    /** @var bool */
    private $removeOnEmpty = false;

    /**
     * @return bool
     */
    public function isRemoveOnEmpty(): bool
    {
        return $this->removeOnEmpty;
    }

    /**
     * @param bool $removeOnEmpty
     * @return OneToOne
     */
    public function setRemoveOnEmpty(bool $removeOnEmpty): OneToOne
    {
        $this->removeOnEmpty = $removeOnEmpty;
        return $this;
    }
}