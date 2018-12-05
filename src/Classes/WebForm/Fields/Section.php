<?php


namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use KikCMS\ObjectLists\FieldMap;

class Section extends Field
{
    /** @var FieldMap */
    private $fieldMap;

    /**
     * @param string $key
     * @param Field[] $fields
     */
    public function __construct(string $key, array $fields)
    {
        $this->fieldMap = new FieldMap();
        $this->key      = $key;

        foreach ($fields as $field){
            $this->fieldMap->add($field, $field->getKey());
            $field->setSection($this);
        }
    }

    /**
     * @return FieldMap
     */
    public function getFieldMap(): FieldMap
    {
        return $this->fieldMap;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Field::TYPE_SECTION;
    }
}