<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm;


use KikCMS\Classes\DataTable\SelectDataTable;
use KikCMS\Classes\Phalcon\Validator\ReCaptcha;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\Fields\AutocompleteField;
use KikCMS\Classes\WebForm\Fields\ButtonField;
use KikCMS\Classes\WebForm\Fields\CheckboxField;
use KikCMS\Classes\WebForm\Fields\DateField;
use KikCMS\Classes\WebForm\Fields\FileField;
use KikCMS\Classes\WebForm\Fields\Header;
use KikCMS\Classes\WebForm\Fields\HiddenField;
use KikCMS\Classes\WebForm\Fields\HtmlField;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Classes\WebForm\Fields\MultiCheckboxField;
use KikCMS\Classes\WebForm\Fields\PasswordField;
use KikCMS\Classes\WebForm\Fields\RadioButtonField;
use KikCMS\Classes\WebForm\Fields\ReCaptchaField;
use KikCMS\Classes\WebForm\Fields\Section;
use KikCMS\Classes\WebForm\Fields\SelectDataTableField;
use KikCMS\Classes\WebForm\Fields\SelectField;
use KikCMS\Classes\WebForm\Fields\TextareaField;
use KikCMS\Classes\WebForm\Fields\TextField;
use KikCMS\Classes\WebForm\Fields\WysiwygField;

/**
 * @property Translator $translator
 */
trait FieldShortcuts
{
    /**
     * @param Field $field
     * @return Field
     */
    public abstract function addField(Field $field);

    /**
     * @param string $key
     * @param string $label
     * @param string $route
     * @param array $validators
     * @return AutocompleteField|Field
     */
    public function addAutoCompleteField(string $key, string $label, string $route, array $validators = []): Field
    {
        return $this->addField(new AutocompleteField($key, $label, $route, $validators));
    }

    /**
     * @param string $key
     * @param string $label
     * @param string $info
     * @param string $buttonLabel
     * @param string $route
     * @return ButtonField|Field
     */
    public function addButtonField(string $key, string $label, string $info, string $buttonLabel, string $route): Field
    {
        return $this->addField(new ButtonField($key, $label, $info, $buttonLabel, $route));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return CheckboxField|Field
     */
    public function addCheckboxField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new CheckboxField($key, $label, $validators));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return DateField|Field
     */
    public function addDateField(string $key, string $label, array $validators = []): DateField
    {
        $format = $this->translator->tl('system.phpDateFormat');

        $dateField = (new DateField($key, $label, $validators))
            ->setFormat($format);

        return $this->addField($dateField);
    }

    /**
     * @param string $key
     * @param string $dataTableClass
     * @param string $label
     *
     * @return Field|DataTableField
     */
    public function addDataTableField(string $key, string $dataTableClass, string $label)
    {
        return $this->addField(new DataTableField($key, $dataTableClass, $label));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|FileField
     */
    public function addFileField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new FileField($key, $label, $validators));
    }

    /**
     * @param string $label
     * @return Header
     */
    public function addHeader(string $label): Field
    {
        return $this->addField(new Header($label));
    }

    /**
     * Add HTML to a form
     *
     * @param string $key
     * @param string $label
     * @param string $content
     * @return Field|HtmlField
     */
    public function addHtmlField(string $key, ?string $label, string $content): Field
    {
        return $this->addField(new HtmlField($key, $label, $content));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|PasswordField
     */
    public function addPasswordField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new PasswordField($key, $label, $validators));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     * @param array $validators
     * @return Field|MultiCheckboxField
     */
    public function addMultiCheckboxField(string $key, string $label, array $options, array $validators = []): Field
    {
        return $this->addField(new MultiCheckboxField($key, $label, $options, $validators));
    }

    /**
     * @param string $key
     * @param SelectDataTable $dataTable
     * @param string $label
     * @return Field|SelectDataTableField
     */
    public function addDataTableSelectField(string $key, SelectDataTable $dataTable, string $label)
    {
        return $this->addField(new SelectDataTableField($key, $dataTable, $label));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     * @param array $validators
     * @return Field|RadioButtonField
     */
    public function addRadioButtonField(string $key, string $label, array $options, array $validators = []): Field
    {
        return $this->addField(new RadioButtonField($key, $label, $options, $validators));
    }

    /**
     * @param string $label
     * @param int $version (2 or 3)
     * @return Field|ReCaptchaField
     */
    public function addRecaptchaField(string $label = null, int $version = 2): Field
    {
        $siteKey = $this->config->recaptcha->siteKey;

        $this->view->assets->addJs('https://www.google.com/recaptcha/api.js?render=' . $siteKey);
        $this->view->reCaptchaSiteKey = $siteKey;

        $validators = $version == 2 ? [new ReCaptcha] : [];

        return $this->addField(new ReCaptchaField($label, $version, $validators));
    }

    /**
     * @param string $key
     * @param array $fields
     * @return Section
     */
    public function addSection(string $key, array $fields): Field
    {
        return $this->addField(new Section($key, $fields));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     * @param array $validators
     * @return Field|SelectField
     */
    public function addSelectField(string $key, string $label, array $options, array $validators = []): Field
    {
        return $this->addField(new SelectField($key, $label, $options, $validators));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field
     */
    public function addTextField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new TextField($key, $label, $validators));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|TextareaField
     */
    public function addTextAreaField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new TextareaField($key, $label, $validators));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|WysiwygField
     */
    public function addWysiwygField(string $key, string $label, array $validators = []): Field
    {
        return $this->addField(new WysiwygField($key, $label, $validators));
    }

    /**
     * @param string $key
     * @param mixed $defaultValue
     * @return Field
     */
    public function addHiddenField(string $key, $defaultValue = null): Field
    {
        return $this->addField(new HiddenField($key, $defaultValue));
    }
}