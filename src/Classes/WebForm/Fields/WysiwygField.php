<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use KikCMS\Classes\WebForm\WebForm;
use Phalcon\Forms\Element\TextArea;

class WysiwygField extends Field
{
    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $validators = [])
    {
        $element = (new TextArea($key))
            ->setLabel($label)
            ->setAttribute('style', 'height: 350px')
            ->setAttribute('class', 'form-control wysiwyg')
            ->setAttribute('id', $key . '_' . uniqid())
            ->addValidators($validators);

        $this->element = $element;
        $this->key     = $key;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return Field::TYPE_WYSIWYG;
    }

    /**
     * @param WebForm $form
     * @return Field|$this
     */
    public function setForm(WebForm $form): Field|static
    {
        parent::setForm($form);

        if($this->getForm()->config->application->tinyMceClick){
            $this->addClass('click-to-activate');
        }

        return $this;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight(int $height): WysiwygField
    {
        $this->getElement()->setAttribute('style', 'height: ' . $height . 'px;');
        return $this;
    }
}