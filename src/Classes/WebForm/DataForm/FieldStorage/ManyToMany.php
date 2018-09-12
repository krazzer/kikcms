<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;

/**
 * Used for MultiCheckbox and DataTableSelect fields, which can be saved in a new row per item
 * @deprecated Use RelationKeys instead
 */
class ManyToMany extends FieldStorage
{
    /** @var null|string use this property to set the db-field to store the key of the form-field */
    private $keyField;

    /**
     * @return null|string
     */
    public function getKeyField()
    {
        return $this->keyField;
    }

    /**
     * @param null|string $keyField
     * @return ManyToMany
     */
    public function setKeyField($keyField)
    {
        $this->keyField = $keyField;
        return $this;
    }
}