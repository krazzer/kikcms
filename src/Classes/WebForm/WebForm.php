<?php

namespace KikCMS\Classes\WebForm;

use InvalidArgumentException;
use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\DataTable\SelectDataTable;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Classes\Phalcon\FormElements\MultiCheck;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\DataForm\FieldStorage\OneToMany;
use KikCMS\Classes\WebForm\Fields\Autocomplete;
use KikCMS\Classes\WebForm\Fields\Button;
use KikCMS\Classes\WebForm\Fields\Checkbox;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Classes\WebForm\Fields\FileField;
use KikCMS\Classes\WebForm\Fields\Hidden as HiddenField;
use KikCMS\Classes\WebForm\Fields\Textarea as TextareaField;
use KikCMS\Classes\WebForm\Fields\MultiCheckbox;
use KikCMS\Classes\WebForm\Fields\SelectDataTableField;
use KikCMS\Classes\WebForm\Fields\Wysiwyg;
use KikCMS\Config\StatusCodes;
use KikCMS\ObjectLists\FieldMap;
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

    /** @var FieldMap */
    protected $fieldMap;

    /** @var Tab[] */
    protected $tabs = [];

    /** @var array tracks field key increments */
    protected $keys = [];

    /** @var string */
    protected $formTemplate = 'form';

    /** @var bool */
    protected $initialized = false;

    /** @var bool */
    protected $showRequiredMessage = false;

    /** @var bool */
    protected $displaySendButton = true;

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

    /**
     * @inheritdoc
     */
    public function __construct(Filters $filters = null)
    {
        parent::__construct($filters);

        $this->form = new Form();
        $this->form->setValidation($this->validation);

        $this->fieldMap = new FieldMap();

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
        $this->view->assets->addJs('cmsassets/js/webform/webform.js?v=1.029');
        $this->view->assets->addCss('cmsassets/css/webform.css');

        if ($this->hasFieldWithType(Field::TYPE_WYSIWYG)) {
            $this->view->assets->addCss('cmsassets/css/tinymce/editor.css');
        }

        if ($this->hasFieldWithType(Field::TYPE_FILE)) {
            (new Finder())->addAssets();
        }

        if ($this->hasFieldWithType(Field::TYPE_DATE)) {
            $langCode = $this->translator->tl('system.langCode');
            $this->view->assets->addJs('cmsassets/js/vendor/moment/moment.js');
            $this->view->assets->addJs('cmsassets/js/vendor/moment/' . $langCode . '.js');
        }
    }

    /**
     * @param Field $field
     * @return Field
     */
    public function addField(Field $field): Field
    {
        if ($field->getElement()) {
            $field->setKey($field->getElement()->getName());
        }

        $key = $field->getKey();
        $field->setColumn($key);

        if (array_key_exists($key, $this->keys)) {
            $newKey             = $key . (count($this->keys[$key]) + 1);
            $this->keys[$key][] = $newKey;

            $field->setKey($newKey);
        } else {
            $this->keys[$key] = [$key];
        }

        $field->setForm($this);
        $this->fieldMap->add($field, $field->getKey());

        if ($field->getElement()) {
            $this->form->add($field->getElement());
        }

        return $field;
    }

    /**
     * @param string $key
     * @param string $label
     * @param string $route
     * @param array $validators
     * @return Field|Autocomplete
     */
    public function addAutoCompleteField(string $key, string $label, string $route, array $validators = []): Field
    {
        $element = (new Text($key))
            ->setLabel($label)
            ->setAttribute('class', 'form-control autocomplete')
            ->setAttribute('autocomplete', 'off')
            ->setAttribute('data-field-key', $key)
            ->setAttribute('data-route', $route)
            ->addValidators($validators);

        return $this->addField(new Autocomplete($element));
    }

    /**
     * Add a button. Can be used if specific functionality is managed somewhere else than in this form.
     *
     * @param string $label
     * @param string $info
     * @param string $buttonLabel
     * @param string $route
     * @return Field|Button
     */
    public function addButtonField(string $label, string $info, string $buttonLabel, string $route)
    {
        $button = (new Button())
            ->setKey('button')
            ->setInfo($info)
            ->setLabel($label)
            ->setButtonLabel($buttonLabel)
            ->setRoute($route);

        return $this->addField($button);
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
     * @param DataTable $dataTable
     * @param string $label
     *
     * @return Field|DataTableField
     */
    public function addDataTableField(DataTable $dataTable, string $label)
    {
        $dataTableElement = new Hidden('dt');
        $dataTableElement->setLabel($label);
        $dataTableElement->setDefault($dataTable->getInstance());

        $dataTableField = $this->addField(new DataTableField($dataTableElement, $dataTable));

        $storage = (new OneToMany())
            ->setTableModel($dataTable->getModel());

        $dataTableField->store($storage);

        return $dataTableField;
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
     * @param SelectDataTable $dataTable
     * @param string $label
     * @return Field|SelectDataTableField
     */
    public function addDataTableSelectField(string $key, SelectDataTable $dataTable, string $label)
    {
        $element = new Hidden($key);
        $element->setLabel($label);

        $dataTableField = $this->addField(new SelectDataTableField($element, $dataTable));

        return $dataTableField;
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
     * @return Field|TextareaField
     */
    public function addTextAreaField(string $key, string $label, array $validators = []): Field
    {
        $element = (new TextArea($key))
            ->setLabel($label)
            ->setAttribute('class', 'form-control')
            ->addValidators($validators);

        return $this->addField(new TextareaField($element));
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field|Wysiwyg
     */
    public function addWysiwygField(string $key, string $label, array $validators = []): Field
    {
        $element = (new TextArea($key))
            ->setLabel($label)
            ->setAttribute('style', 'height: 350px')
            ->setAttribute('class', 'form-control wysiwyg')
            ->setAttribute('id', $key . '_' . uniqid())
            ->addValidators($validators);

        return $this->addField(new Wysiwyg($element));
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
     * @return mixed
     */
    public function getCurrentTab()
    {
        return $this->request->getPost('currentTab', null, 0);
    }

    /**
     * @param string $fieldKey
     * @return null|ElementInterface
     */
    public function getElement(string $fieldKey): ?ElementInterface
    {
        if ( ! $this->form->has($fieldKey)) {
            return null;
        }

        return $this->form->get($fieldKey);
    }

    /**
     * @return FieldMap
     */
    public function getFieldMap(): FieldMap
    {
        return $this->fieldMap;
    }

    /**
     * Get the form's input. The input returned will not be raw, but converted to PHP objects,
     * e.g. a json encoded object will be converted to an PHP object
     *
     * @return array
     */
    public function getInput(): array
    {
        $input = $this->request->getPost();

        foreach ($input as $key => $value) {
            if ( ! $this->fieldMap->has($key)) {
                continue;
            }

            $input[$key] = $this->fieldMap->get($key)->getInput($value);
        }

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
     * @return bool
     */
    public function getShowRequiredMessage(): bool
    {
        return $this->showRequiredMessage;
    }

    /**
     * Render the form
     *
     * @return Response|string
     */
    public function render()
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

                if (is_string($result) || $result instanceof Response) {
                    return $result;
                }
            } else {
                $this->response->setStatusCode(StatusCodes::FORM_INVALID, StatusCodes::FORM_INVALID_MESSAGE);
            }
        }

        return $this->renderForm($errorContainer);
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
     *
     * @return WebForm|$this
     */
    public function initializeForm()
    {
        if ($this->initialized) {
            return $this;
        }

        $this->initialize();
        $this->initialized = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmptyFilters(): Filters
    {
        return new Filters();
    }

    /**
     * Initialize fields
     */
    protected function initializeFields()
    {
        foreach ($this->fieldMap as $field) {
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
     * @param DataTableField $field
     */
    protected function renderDataTableField(DataTableField $field)
    {
        $renderedDataTable = $field->getDataTable()->render();
        $field->setRenderedDataTable($renderedDataTable);
    }

    /**
     * Pre-renders the DataTable fields, so that any required asset will be correctly added
     */
    protected function renderDataTableFields()
    {
        /** @var DataTableField|SelectDataTableField $field */
        foreach ($this->getFieldMap() as $key => $field) {
            if ($field->getType() == Field::TYPE_SELECT_DATA_TABLE) {
                $this->renderSelectDataTableField($field);
            }

            if ($field->getType() == Field::TYPE_DATA_TABLE) {
                $this->renderDataTableField($field);
            }
        }
    }

    /**
     * @param ErrorContainer $errorContainer
     * @return string
     */
    protected function renderForm(ErrorContainer $errorContainer)
    {
        $this->renderDataTableFields();

        return $this->renderView($this->formTemplate, [
            'form'               => $this->form,
            'fields'             => $this->fieldMap,
            'tabs'               => $this->tabs,
            'filters'            => $this->filters,
            'displaySendButton'  => $this->displaySendButton,
            'security'           => $this->security,
            'currentTab'         => $this->getCurrentTab(),
            'fieldsWithoutTab'   => $this->getFieldsWithoutTab(),
            'formId'             => $this->getFormId(),
            'sendButtonLabel'    => $this->getSendLabel(),
            'placeHolderAsLabel' => $this->isPlaceHolderAsLabel(),
            'instance'           => $this->getInstance(),
            'jsData'             => $this->getJsData(),
            'class'              => static::class,
            'errorContainer'     => $errorContainer,
            'webForm'            => $this,
        ]);
    }

    /**
     * @param SelectDataTableField $field
     */
    protected function renderSelectDataTableField(SelectDataTableField $field)
    {
        // set selected ids filter for SelectDataTable
        if ($field->getElement()->getValue()) {
            $filters = $field->getDataTable()->getFilters();
            $filters->setSelectedValues(json_decode($field->getElement()->getValue()));
        }

        $field->setRenderedDataTable($field->getDataTable()->render());
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

        foreach ($this->fieldMap as $field) {
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
        foreach ($this->fieldMap as $field) {
            if ($field->getType() == $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update the forms' input after a post is done
     */
    private function updateFieldsByPostData()
    {
        foreach ($this->fieldMap as $key => $field) {
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