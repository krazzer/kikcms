<?php

namespace KikCMS\Classes\WebForm;

use InvalidArgumentException;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Classes\Phalcon\FormElements\MultiCheck;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\Fields\Autocomplete;
use KikCMS\Classes\WebForm\Fields\Checkbox;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Classes\WebForm\Fields\FileField;
use KikCMS\Classes\WebForm\Fields\Hidden as HiddenField;
use KikCMS\Classes\WebForm\Fields\MultiCheckbox;
use KikCMS\Classes\WebForm\Fields\Wysiwyg;
use KikCMS\Config\StatusCodes;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\ElementInterface;
use Phalcon\Forms\Form;
use Phalcon\Http\Response;
use Phalcon\Mvc\View;
use Phalcon\Validation;

/**
 * @property View $view
 * @property Validation $validation
 * @property Translator $translator
 */
abstract class WebForm extends Renderable
{
    const WEB_FORM_ID    = 'webFormId';
    const WEB_FORM_CLASS = 'webFormClass';

    /** @var Field[] */
    protected $fields = [];

    /** @var Tab[] */
    protected $tabs = [];

    /** @var array tracks field key increments */
    protected $keys = [];

    /** @var string */
    protected $formTemplate = 'form';

    /** @var bool */
    protected $initialized = false;

    /** @inheritdoc */
    protected $instancePrefix = 'webForm';

    /** @inheritdoc */
    protected $jsClass = 'WebForm';

    /** @inheritdoc */
    protected $viewDirectory = 'webform';

    /** @var Form */
    private $form;

    /** @var string */
    private $sendLabel;

    /** @var bool */
    private $placeHolderAsLabel = false;

    /** @var callable */
    private $successAction;

    /** @var callable */
    private $validateAction;

    public function __construct()
    {
        parent::__construct();

        $this->form = new Form();
        $this->form->setValidation($this->validation);

        $this->sendLabel = $this->translator->tl('webform.defaultSendLabel');
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Adds required assets
     */
    public function addAssets()
    {
        $this->view->assets->addJs('cmsassets/js/webform/webform.js');
        $this->view->assets->addCss('cmsassets/css/webform.css');

        if ($this->hasFieldWithType(Field::TYPE_WYSIWYG)) {
            $this->view->assets->addCss('cmsassets/css/tinymce/editor.css');
        }

        if ($this->hasFieldWithType(Field::TYPE_AUTOCOMPLETE)) {
            $this->view->assets->addJs('cmsassets/js/vendor/typeahead.js');
        }

        if ($this->hasFieldWithType(Field::TYPE_FILE)) {
            (new Finder())->addAssets();
        }

        if ($this->hasFieldWithType(Field::TYPE_DATE)) {
            $langCode = $this->translator->tl('system.langCode');
            $this->view->assets->addJs('cmsassets/js/vendor/moment/moment.js');
            $this->view->assets->addJs('cmsassets/js/vendor/moment/' . $langCode . '.js');
            $this->view->assets->addJs('cmsassets/js/vendor/bootstrap/transition.js');
            $this->view->assets->addJs('cmsassets/js/vendor/bootstrap/collapse.js');
            $this->view->assets->addJs('cmsassets/js/vendor/bootstrap/bootstrap-datetimepicker.min.js');
            $this->view->assets->addCss('cmsassets/css/vendor/bootstrap-datetimepicker.min.css');
        }
    }

    /**
     * @param Field $field
     * @return Field
     */
    public function addField(Field $field): Field
    {
        $key = $field->getKey();
        $field->setTableField($key);

        if (array_key_exists($key, $this->keys)) {
            $newKey             = $key . (count($this->keys[$key]) + 1);
            $this->keys[$key][] = $newKey;
            $field->getElement()->setName($newKey);
        } else {
            $this->keys[$key] = [$key];
        }

        $field->setForm($this);
        $this->fields[$field->getKey()] = $field;
        $this->form->add($field->getElement());

        return $field;
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|Autocomplete
     */
    public function addAutoCompleteField(string $key, string $label, array $validators = []): Field
    {
        $autoComplete = new Text($key);
        $autoComplete->setLabel($label);
        $autoComplete->setAttribute('class', 'form-control autocomplete');
        $autoComplete->setAttribute('autocomplete', 'off');
        $autoComplete->setAttribute('data-field-key', $key);
        $autoComplete->addValidators($validators);

        return $this->addField(new Autocomplete($autoComplete));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|Checkbox
     */
    public function addCheckboxField(string $key, string $label, array $validators = []): Field
    {
        $checkbox = new Check($key);
        $checkbox->setLabel($label);
        $checkbox->setAttribute('type', 'checkbox');
        $checkbox->addValidators($validators);

        return $this->addField(new Checkbox($checkbox));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Fields\Date|Field
     */
    public function addDateField(string $key, string $label, array $validators = []): Field
    {
        $phpDateFormat      = $this->translator->tl('system.phpDateFormat');
        $momentJsDateFormat = $this->translator->tl('system.momentJsDateFormat');

        $validators[] = new \KikCMS\Classes\Phalcon\Validator\Date([
            "format"     => $phpDateFormat,
            "allowEmpty" => true,
        ]);

        $date = new Date($key);
        $date->setLabel($label);
        $date->setAttribute('class', 'form-control');
        $date->setAttribute('data-format', $momentJsDateFormat);
        $date->addValidators($validators);

        return $this->addField(new Fields\Date($date));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|FileField
     */
    public function addFileField(string $key, string $label, array $validators = []): Field
    {
        $file = new Hidden($key);
        $file->setLabel($label);
        $file->addValidators($validators);
        $file->setAttribute('class', 'fileId');

        return $this->addField(new FileField($file));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field
     */
    public function addPasswordField(string $key, string $label, array $validators = []): Field
    {
        $password = new Password($key);
        $password->setLabel($label);
        $password->setAttribute('class', 'form-control');
        $password->addValidators($validators);

        return $this->addField(new Field($password));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     *
     * @return Field|MultiCheckbox
     */
    public function addMultiCheckboxField(string $key, string $label, array $options): Field
    {
        $multiCheckbox = new MultiCheck($key);
        $multiCheckbox->setAttribute('type', 'multiCheckbox');
        $multiCheckbox->setLabel($label);
        $multiCheckbox->setOptions($options);

        return $this->addField(new MultiCheckbox($multiCheckbox));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     * @param array $validators
     * @return Field
     */
    public function addSelectField(string $key, string $label, array $options, array $validators = []): Field
    {
        $select = new Select($key);
        $select->setLabel($label);
        $select->addValidators($validators);
        $select->setOptions($options);
        $select->setAttribute('class', 'form-control');

        return $this->addField(new Field($select));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field
     */
    public function addTextField(string $key, string $label, array $validators = []): Field
    {
        $name = new Text($key);
        $name->setLabel($label);
        $name->setAttribute('class', 'form-control');
        $name->addValidators($validators);

        return $this->addField(new Field($name));
    }

    /**
     * @param string $name
     * @param Field[] $fields
     */
    public function addTab(string $name, array $fields)
    {
        $tab = new Tab($name, $fields);

        foreach ($fields as $key => $field) {
            $field->setTab($tab);
        }

        $this->tabs[] = $tab;
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field
     */
    public function addTextAreaField(string $key, string $label, array $validators = []): Field
    {
        $name = new TextArea($key);
        $name->setLabel($label);
        $name->setAttribute('class', 'form-control');
        $name->addValidators($validators);

        return $this->addField(new Field($name));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|Wysiwyg
     */
    public function addWysiwygField(string $key, string $label, array $validators = []): Field
    {
        $name = new TextArea($key);
        $name->setLabel($label);
        $name->setAttribute('style', 'height: 350px');
        $name->setAttribute('class', 'form-control wysiwyg');
        $name->setAttribute('id', $key . '_' . uniqid());
        $name->addValidators($validators);

        return $this->addField(new Wysiwyg($name));
    }

    /**
     * @param string $key
     * @param mixed $defaultValue
     * @return Field
     */
    public function addHiddenField(string $key, $defaultValue = null): Field
    {
        $hidden = new Hidden($key);
        $hidden->setDefault($defaultValue);
        $hidden->setAttribute('type', 'hidden');

        return $this->addField(new HiddenField($hidden));
    }

    /**
     * @param string $fieldKey
     * @return bool
     */
    public function hasField(string $fieldKey): bool
    {
        return $this->form->has($fieldKey);
    }

    /**
     * @return mixed
     */
    public function getCurrentTab()
    {
        return $this->request->getPost('currentTab', null, 0);
    }

    /**
     * @param string $fieldKey
     * @return ElementInterface
     */
    public function getElement(string $fieldKey): ElementInterface
    {
        if ( ! $this->form->has($fieldKey)) {
            return null;
        }

        return $this->form->get($fieldKey);
    }

    /**
     * @param string $fieldKey
     * @return Field
     */
    public function getField(string $fieldKey): Field
    {
        return $this->fields[$fieldKey];
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getInput(): array
    {
        $input = $this->request->getPost();

        return $input;
    }

    /**
     * @return Tab[]
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * Render the form
     *
     * @return Response|string
     */
    public function render(): string
    {
        $errorContainer = new ErrorContainer();

        $this->initializeForm();
        $this->initializeFields();
        $this->addAssets();

        if ($this->isPosted()) {
            $errorContainer = $this->getErrors();
            $this->updateFieldsByPostData();

            if ($errorContainer->isEmpty()) {
                $result = $this->successAction($this->getInput());

                if (is_string($result)) {
                    return $result;
                }
            } else {
                $this->response->setStatusCode(StatusCodes::FORM_INVALID, StatusCodes::FORM_INVALID_MESSAGE);
            }
        }

        $this->renderDataTableFields();

        return $this->renderView($this->formTemplate, [
            'form'               => $this->form,
            'fields'             => $this->fields,
            'tabs'               => $this->tabs,
            'filters'            => $this->filters,
            'currentTab'         => $this->getCurrentTab(),
            'fieldsWithoutTab'   => $this->getFieldsWithoutTab(),
            'formId'             => $this->getFormId(),
            'sendButtonLabel'    => $this->getSendLabel(),
            'placeHolderAsLabel' => $this->isPlaceHolderAsLabel(),
            'instance'           => $this->getInstance(),
            'jsData'             => $this->getJsData(),
            'errorContainer'     => $errorContainer,
            'security'           => $this->security,
            'class'              => static::class,
        ]);
    }

    /**
     * @param string $sendLabel
     *
     * @return WebForm|$this
     */
    public function setSendLabel(string $sendLabel)
    {
        $this->sendLabel = $sendLabel;
        return $this;
    }

    /**
     * @return string
     */
    private function getFormId(): string
    {
        return str_replace('\\', '', static::class);
    }

    /**
     * @return string
     */
    public function getSendLabel(): string
    {
        return $this->sendLabel;
    }

    /**
     * @return boolean
     */
    public function isPlaceHolderAsLabel(): bool
    {
        return $this->placeHolderAsLabel;
    }

    /**
     * Checks whether this form has been posted
     */
    public function isPosted()
    {
        return $this->request->isPost() && $this->request->get(self::WEB_FORM_ID) == $this->getFormId();
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param boolean $placeHolderAsLabel
     *
     * @return WebForm|$this
     */
    public function setPlaceHolderAsLabel(bool $placeHolderAsLabel)
    {
        $this->placeHolderAsLabel = $placeHolderAsLabel;

        return $this;
    }

    /**
     * @param callable $successAction
     * @return WebForm|$this
     */
    public function setSuccessAction(callable $successAction): WebForm
    {
        $this->successAction = $successAction;

        return $this;
    }

    /**
     * @param callable $validateAction
     * @return WebForm|$this
     */
    public function setValidateAction(callable $validateAction): WebForm
    {
        $this->validateAction = $validateAction;

        return $this;
    }

    /**
     * Override to build up the form
     */
    public function initializeForm()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialize();
        $this->initialized = true;
    }

    /**
     * @inheritdoc
     */
    protected function getEmptyFilters(): Filters
    {
        return new Filters();
    }

    /**
     * Initialize fields
     */
    protected function initializeFields()
    {
        foreach ($this->fields as $field) {
            if ($this->isPlaceHolderAsLabel()) {
                $field->getElement()->setAttribute('placeholder', $field->getElement()->getLabel());
            }
        }

        $this->addHiddenField(self::WEB_FORM_ID, $this->getFormId());
    }

    /**
     * Verify the input with additional rules, if the input was valid, i.e. checking login credentials
     * Override this method if you have such rules
     *
     * @param array $input
     * @return ErrorContainer
     */
    protected function validate(array $input): ErrorContainer
    {
        if ($this->validateAction) {
            return call_user_func($this->validateAction, $input);
        }

        return new ErrorContainer();
    }

    /**
     * This is executed when your form has been successfully send
     * By default it returns a string but you can also redirect to a thank you page for instance
     *
     * @param array $input
     * @return bool|Response|string
     */
    protected function successAction(array $input)
    {
        if ($this->successAction) {
            return call_user_func($this->successAction, $input);
        }

        throw new InvalidArgumentException('Method Webform::successAction must be overridden, or 
            Webform::$successAction must be set.');
    }

    /**
     * @return ErrorContainer
     */
    private function getErrors(): ErrorContainer
    {
        $errorContainer = $this->validate($this->getInput());

        if ( ! $this->security->checkToken()) {
            $errorContainer->addFormError($this->translator->tl('webform.messages.csrf'));
        }

        if ($this->form->isValid($this->getInput()) && $errorContainer->isEmpty()) {
            return $errorContainer;
        }

        foreach ($this->form->getElements() as $formElement) {
            $elementName     = $formElement->getName();
            $elementMessages = $this->form->getMessagesFor($elementName);

            if ( ! $elementMessages) {
                continue;
            }

            foreach ($elementMessages as $message) {
                $message = $message->getMessage();
                $message = str_replace(':label', "'" . $formElement->getLabel() . "'", $message);

                $errorContainer->addFieldError($elementName, $message);
            }

            $class = $formElement->getAttribute('class');
            $class = $class ? $class . ' has-errors' : 'has-errors';

            $formElement->setAttribute('class', $class);
        }

        // add a global error message if there are field errors but no form errors
        if ( ! $errorContainer->hasFormErrors() && $errorContainer->hasFieldErrors()) {
            $errorContainer->addFormError($this->translator->tl('webform.messages.fieldErrors'));
        }

        return $errorContainer;
    }

    /**
     * return Field[]
     */
    private function getFieldsWithoutTab()
    {
        $fieldsWithoutTab = [];

        foreach ($this->fields as $field) {
            if ( ! $field->getTab()) {
                $fieldsWithoutTab[] = $field;
            }
        }

        return $fieldsWithoutTab;
    }

    /**
     * @param string $type
     * @return bool
     */
    private function hasFieldWithType(string $type): bool
    {
        foreach ($this->fields as $field) {
            if ($field->getType() == $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Pre-renders the DataTable fields, so that any required asset will be correctly added
     */
    private function renderDataTableFields()
    {
        $parentEditId = 0;

        // if a new id is saved, the field with key editId is set, so we pass it to the subDataTable
        if ($this->hasField(DataTable::EDIT_ID)) {
            $parentEditId = $this->getField(DataTable::EDIT_ID)->getElement()->getValue();
        }

        /** @var DataTableField $field */
        foreach ($this->getFields() as $key => $field) {
            if ($field->getType() != Field::TYPE_DATA_TABLE) {
                continue;
            }

            $field->getDataTable()->getFilters()->setParentEditId($parentEditId);

            $renderedDataTable = $field->getDataTable()->render();

            $field->setRenderedDataTable($renderedDataTable);
        }
    }

    /**
     * Update the forms' input after a post is done
     */
    private function updateFieldsByPostData()
    {
        foreach ($this->fields as $key => $field) {
            // set unposted checkboxes to default 0
            if ($field->getType() == Field::TYPE_CHECKBOX && ! $this->request->hasPost($key)) {
                $field->setDefault(0);
            }

            // re-use earlier generated dataTable instance
            if ($field->getType() == Field::TYPE_DATA_TABLE && $this->request->hasPost($key)) {
                $instance = $this->request->getPost($key);
                /** @var DataTableField $field */
                $field->getDataTable()->setInstance($instance);
                $field->setDefault($instance);
            }
        }
    }
}