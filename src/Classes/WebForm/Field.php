<?php

namespace KikCMS\Classes\WebForm;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use Phalcon\Forms\Element;
use Phalcon\Forms\ElementInterface;

/**
 * Represents a field of a form
 */
class Field
{
    const TYPE_AUTOCOMPLETE      = 'autocomplete';
    const TYPE_BUTTON            = 'button';
    const TYPE_CHECKBOX          = 'checkbox';
    const TYPE_DATA_TABLE        = 'dataTable';
    const TYPE_KEYED_DATA_TABLE  = 'keyedDataTable';
    const TYPE_DATE              = 'date';
    const TYPE_FILE              = 'file';
    const TYPE_HIDDEN            = 'hidden';
    const TYPE_HTML              = 'html';
    const TYPE_MULTI_CHECKBOX    = 'multiCheckbox';
    const TYPE_SELECT            = 'select';
    const TYPE_RADIOBUTTON       = 'radioButton';
    const TYPE_SELECT_DATA_TABLE = 'selectDataTable';
    const TYPE_TEXTAREA          = 'textarea';
    const TYPE_WYSIWYG           = 'wysiwyg';
    const TYPE_RECAPTCHA         = 'reCaptcha';
    const TYPE_PASSWORD          = 'password';

    /** @var WebForm|DataForm */
    protected $form;

    /** @var Element|null */
    protected $element;

    /** @var string */
    protected $key;

    /** @var array */
    private $cssClasses = [];

    /** @var Tab */
    private $tab;

    /** @var string the table column where the value should be saved */
    private $column;

    /** @var bool whether this field is required or not, note that this does nothing with validation */
    private $required = false;

    /** @var string|null */
    private $helpText;

    /** @var bool */
    private $dontStore = false;

    /**
     * Add a css class to the field wrapper
     *
     * @param string $class
     * @return Field|$this
     */
    public function addClass(string $class): Field
    {
        $this->cssClasses[] = $class;

        return $this;
    }

    /**
     * Add a css class to the element
     *
     * @param string $class
     * @return $this|Field
     */
    public function addElementClass(string $class)
    {
        $classes = explode(' ', $this->getElement()->getAttribute('class'));

        if ( ! in_array($class, $classes)) {
            $classes[] = $class;
        }

        $this->getElement()->setAttribute('class', implode(' ', $classes));

        return $this;
    }

    /**
     * @param $value
     * @return $this|Field
     */
    public function setDefault($value): Field
    {
        $this->element->setDefault($value);

        return $this;
    }

    /**
     * @param string $value
     * @return $this|Field
     */
    public function setPlaceholder(string $value): Field
    {
        $this->setAttribute('placeholder', $value);

        return $this;
    }

    /**
     * @return array
     */
    public function getClasses(): array
    {
        return $this->cssClasses;
    }

    /**
     * @return null|Element
     */
    public function getElement(): ?Element
    {
        return $this->element;
    }

    /**
     * @return WebForm|DataForm
     */
    public function getForm(): WebForm
    {
        return $this->form;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     *
     * @return $this|Field
     */
    public function setAttribute(string $attribute, $value)
    {
        $this->element->setAttribute($attribute, $value);

        return $this;
    }

    /**
     * @param Element|ElementInterface $element
     * @return $this|Field
     */
    public function setElement(Element $element): Field
    {
        $this->element = $element;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this|Field
     */
    public function setKey(string $key)
    {
        $this->key = $key;

        if ($this->element) {
            $this->element->setName($key);
        }

        return $this;
    }

    /**
     * @param Webform $form
     * @return $this|Field
     */
    public function setForm(WebForm $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    /**
     * @param null|string $helpText
     * @return $this|Field
     */
    public function setHelpText($helpText)
    {
        $this->helpText = $helpText;
        return $this;
    }

    /**
     * Set the elements' maxlength attribute
     *
     * @param int $maxLength
     * @return $this|Field
     */
    public function setMaxLength(int $maxLength)
    {
        $this->getElement()->setAttribute('maxlength', $maxLength);
        return $this;
    }

    /**
     * @return bool
     */
    public function isDontStore(): bool
    {
        return $this->dontStore;
    }

    /**
     * Override this method to convert the field's value to a format best handled by PHP
     * e.g. convert a json encoded object to an actual PHP object
     *
     * @param $value
     * @return mixed
     */
    public function getInput($value)
    {
        return $value;
    }

    /**
     * Override this method to convert the field's value to a format that is required in the form
     * e.g. json encode an array or object
     *
     * @param $value
     * @return mixed
     */
    public function getFormFormat($value)
    {
        return $value;
    }

    /**
     * @return null|Tab
     */
    public function getTab(): ?Tab
    {
        return $this->tab;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return $this|Field
     */
    public function setRequired(bool $required = true): Field
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @param Tab $tab
     * @return Field
     */
    public function setTab(Tab $tab): Field
    {
        $this->tab = $tab;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getColumn(): ?string
    {
        return $this->column;
    }

    /**
     * @param string $column
     * @return Field
     */
    public function setColumn(string $column): Field
    {
        $this->column = $column;
        return $this;
    }

    /**
     * Shortcut to set the storage to None
     * @return Field
     */
    public function dontStore(): Field
    {
        $this->dontStore = true;

        return $this;
    }
}