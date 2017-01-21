<?php

namespace KikCMS\Classes\WebForm;

use InvalidArgumentException;
use KikCMS\Classes\Phalcon\FormElements\MultiCheck;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\Fields\Autocomplete;
use KikCMS\Classes\WebForm\Fields\Checkbox;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Classes\WebForm\Fields\Hidden as HiddenField;
use KikCMS\Classes\WebForm\Fields\MultiCheckbox;
use KikCMS\Classes\WebForm\Fields\Wysiwyg;
use KikCMS\Config\StatusCodes;
use Phalcon\Di\Injectable;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
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
class WebForm extends Injectable
{
    const WEB_FORM_ID = 'webFormId';

    /** @var Field[] */
    protected $fields = [];

    /** @var string */
    protected $formTemplate = 'form';

    /** @var array tracks field key increments */
    protected $keys;

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
        if ($this->hasFieldWithType(Field::TYPE_WYSIWYG)) {
            $this->view->assets->addJs('//cdn.tinymce.com/4/tinymce.min.js');
            $this->view->assets->addCss('cmsassets/css/tinymce/editor.css');
        }

        if ($this->hasFieldWithType(Field::TYPE_AUTOCOMPLETE)) {
            $this->view->assets->addJs('/cmsassets/js/typeahead.js');
        }
    }

    /**
     * @param Field $field
     * @return Field
     */
    public function addField(Field $field): Field
    {
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
     * Render the form
     *
     * @param array $parameters
     * @return Response|string
     */
    public function render($parameters = [])
    {
        $errorContainer = new ErrorContainer();

        $this->initialize();
        $this->initializeFields();
        $this->addAssets();

        if ($this->isPosted()) {
            $errorContainer = $this->getErrors();

            //todo: move this to somewhere else
            // set unposted checkboxes to default 0
            foreach ($this->fields as $key => $field) {
                $element = $field->getElement();

                if ($field->getType() == Field::TYPE_CHECKBOX && ! $this->request->hasPost($key)) {
                    $element->setDefault(0);
                }

                // re-use generated dataTable instance
                /** @var DataTableField $field */
                if ($field->getType() == Field::TYPE_DATA_TABLE && $this->request->hasPost($key)) {
                    $instance = $this->request->getPost($key);
                    $field->getDataTable()->setInstanceName($instance);
                    $this->getField($key)->getElement()->setDefault($instance);
                }
            }

            if ($errorContainer->isEmpty()) {
                $result = $this->successAction($this->getInput());

                if (is_string($result)) {
                    return $result;
                }
            } else {
                $this->response->setStatusCode(StatusCodes::FORM_INVALID, StatusCodes::FORM_INVALID_MESSAGE);
            }
        }

        $defaultParameters = [
            'form'               => $this->form,
            'fields'             => $this->fields,
            'formId'             => $this->getFormId(),
            'sendButtonLabel'    => $this->getSendLabel(),
            'placeHolderAsLabel' => $this->isPlaceHolderAsLabel(),
            'errorContainer'     => $errorContainer,
            'identifier'         => $this->identifier,
            'security'           => $this->security,
        ];

        return $this->renderView($this->formTemplate, array_merge($defaultParameters, $parameters));
    }

    /**
     * Renders a view
     *
     * @param $viewName
     * @param array $parameters
     *
     * @return string
     */
    public function renderView($viewName, array $parameters): string
    {
        return $this->view->getPartial('webform/' . $viewName, $parameters);
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
    protected function initialize()
    {
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
}