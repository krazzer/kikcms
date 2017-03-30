<?php

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\Phalcon\Validator\NameExists;
use KikCMS\Classes\WebForm\DataForm\FieldTransformer\NameToId;
use KikCMS\Classes\WebForm\Field;

class Autocomplete extends Field
{
    /** @var string of the model used for finding results */
    private $sourceModel;

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_AUTOCOMPLETE;
    }

    /**
     * @return string
     */
    public function getSourceModel(): string
    {
        return $this->sourceModel;
    }

    /**
     * @param string $sourceModel
     * @param bool $addValidator set to false if the value can be something else than the model
     * @return $this
     */
    public function setSourceModel(string $sourceModel, $addValidator = true)
    {
        $this->sourceModel = $sourceModel;

        $this->form->addFieldTransformer(new NameToId($this));

        if ($addValidator) {
            $this->getElement()->addValidator(new NameExists([NameExists::OPTION_MODEL => $sourceModel]));
        }

        return $this;
    }
}