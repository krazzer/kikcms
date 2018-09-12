<?php

namespace KikCMS\Classes\WebForm;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\FieldStorage;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\ManyToMany;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\None;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\OneToOne;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\Translation;
use KikCMS\Models\PageContent;
use KikCMS\Models\PageLanguageContent;
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

    /** @var FieldStorage|null contains how this field should be stored */
    protected $storage;

    /** @var string */
    protected $key;

    /** @var Tab */
    private $tab;

    /** @var string the table column where the value should be saved */
    private $column;

    /** @var bool whether this field is required or not, note that this does nothing with validation */
    private $required = false;

    /** @var string|null */
    private $helpText;

    /**
     * Add a class to the element
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
     * @return null|FieldStorage
     * @deprecated Use RelationKeys instead
     */
    public function getStorage(): ?FieldStorage
    {
        return $this->storage;
    }

    /**
     * Shortcut to set the storage to None
     * @return Field
     * @deprecated Use RelationKeys instead
     */
    public function dontStore(): Field
    {
        return $this->store(new None());
    }

    /**
     * Shortcut to set the storage to OneToOne
     *
     * @param string $table
     * @param $relatedField
     * @param bool $addLanguageCode
     * @param array $defaultValues
     *
     * @return $this|Field
     * @deprecated Use RelationKeys instead
     */
    public function table(string $table, $relatedField, $addLanguageCode = false, $defaultValues = [])
    {
        $fieldStorage = (new OneToOne())
            ->setField($this)
            ->setTableModel($table)
            ->setRelatedField($relatedField)
            ->setAddLanguageCode($addLanguageCode)
            ->setDefaultValues($defaultValues);

        $this->store($fieldStorage);

        return $this;
    }

    /**
     * Shortcut to set the storage to ManyToMany
     *
     * @param string $table
     * @param string $relatedField
     * @param bool $addLanguageCode
     * @param array $defaultValues
     * @return $this|Field
     * @deprecated Use RelationKeys instead
     */
    public function tableMultiRow(string $table, string $relatedField, $addLanguageCode = false, $defaultValues = [])
    {
        $fieldStorage = (new ManyToMany())
            ->setField($this)
            ->setTableModel($table)
            ->setRelatedField($relatedField)
            ->setAddLanguageCode($addLanguageCode)
            ->setDefaultValues($defaultValues);

        $this->store($fieldStorage);

        return $this;
    }

    /**
     * Shortcut to set the storage in the cms_translation_value table
     *
     * @param null $langCode
     * @return $this
     * @deprecated Use RelationKeys instead
     */
    public function translate($langCode = null)
    {
        $fieldStorage = (new Translation())
            ->setField($this)
            ->setTableModel($this->form->getModel());

        if ($langCode) {
            $fieldStorage->setLanguageCode($langCode);
        }

        $this->store($fieldStorage);

        return $this;
    }

    /**
     * Shortcut for setting to storage
     *
     * @param FieldStorage $fieldStorage
     * @return Field|$this
     * @deprecated Use RelationKeys instead
     */
    public function store(FieldStorage $fieldStorage): Field
    {
        $this->storage = $fieldStorage;
        return $this;
    }

    /**
     * Shortcut for setting to the default storage of pages
     *
     * @param bool $multiLingual
     * @return Field
     * @deprecated Use RelationKeys instead
     */
    public function storePage($multiLingual = true): Field
    {
        $key = $this->getElement()->getName();

        if ($multiLingual) {
            $this->storage = (new OneToOne())
                ->setTableModel(PageLanguageContent::class)
                ->setRelatedField(PageLanguageContent::FIELD_PAGE_ID)
                ->setDefaultValues([PageLanguageContent::FIELD_FIELD => $key])
                ->setAddLanguageCode(true);
        } else {
            $this->storage = (new OneToOne())
                ->setTableModel(PageContent::class)
                ->setRelatedField(PageContent::FIELD_PAGE_ID)
                ->setDefaultValues([PageContent::FIELD_FIELD => $key]);
        }

        $this->setColumn(PageContent::FIELD_VALUE);

        return $this;
    }
}