<?php

namespace KikCMS\Classes\WebForm;

use InvalidArgumentException;
use KikCMS\Classes\Phalcon\FormElements\MultiCheckbox;
use KikCMS\Classes\Translator;
use KikCMS\Config\StatusCodes;
use Phalcon\Di\Injectable;
use Phalcon\Forms\Element;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
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
    protected $fields;

    /** @var string */
    protected $formTemplate = 'form';

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
     * @param Element $element
     * @param string $type
     * @return Field
     */
    public function addField(Element $element, string $type = 'default'): Field
    {
        $field = $this->createNewField();
        $field->setElement($element);
        $field->setType($type);

        $this->fields[$element->getName()] = $field;
        $this->form->add($element);

        return $field;
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     * @return Field
     */
    public function addCheckboxField(string $key, string $label, array $validators = []): Field
    {
        $checkbox = new Check($key);
        $checkbox->setLabel($label);
        $checkbox->setAttribute('type', 'checkbox');
        $checkbox->addValidators($validators);

        return $this->addField($checkbox, Field::TYPE_CHECKBOX);
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

        return $this->addField($password);
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     *
     * @return Field
     */
    public function addMultiCheckboxField(string $key, string $label, array $options): Field
    {
        $multiCheckbox = new MultiCheckbox($key);
        $multiCheckbox->setAttribute('type', 'multiCheckbox');
        $multiCheckbox->setLabel($label);
        $multiCheckbox->setOptions($options);

        return $this->addField($multiCheckbox, Field::TYPE_MULTI_CHECKBOX);
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

        return $this->addField($name);
    }

    /**
     * @param string $key
     * @param mixed $defaultValue
     * @return Field
     */
    public function addHiddenField(string $key, $defaultValue): Field
    {
        $hidden = new Hidden($key);
        $hidden->setDefault($defaultValue);
        $hidden->setAttribute('type', 'hidden');

        return $this->addField($hidden);
    }

    /**
     * @param string $fieldKey
     * @return ElementInterface
     */
    public function getField(string $fieldKey): ElementInterface
    {
        return $this->form->get($fieldKey);
    }

    /**
     * @return array
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
     * @return string|Response
     */
    public function render()
    {
        $errorContainer = new ErrorContainer();

        $this->initialize();
        $this->initializeFields();

        if ($this->isPosted()) {
            $errorContainer = $this->getErrors();

            // set unposted checkboxes to default 0
            foreach ($this->fields as $key => $field) {
                $element = $field->getElement();

                if ($element->getAttribute('type') == 'checkbox' && ! $this->request->hasPost($key)) {
                    $element->setDefault(0);
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

        return $this->renderView($this->formTemplate, [
            'form'               => $this->form,
            'formId'             => $this->getFormId(),
            'sendButtonLabel'    => $this->getSendLabel(),
            'placeHolderAsLabel' => $this->isPlaceHolderAsLabel(),
            'errorContainer'     => $errorContainer,
            'security'           => $this->security,
            'isDataForm'         => $this instanceof DataForm
        ]);
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
        return $this->view->getRender('webform', $viewName, $parameters);
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
     * @return Field
     */
    protected function createNewField(): Field
    {
        return new Field();
    }

    /**
     * Override to build up the form
     */
    protected function initialize()
    {
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
        $errorContainer = new ErrorContainer();

        if ( ! $this->security->checkToken()) {
            $errorContainer->addFormError($this->translator->tl('webform.messages.csrf'));
        }

        if ($this->form->isValid($this->getInput()) && $errorContainer->isEmpty()) {
            return $this->validate($this->getInput());
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

        return $errorContainer;
    }

    /**
     * Initialize fields
     */
    private function initializeFields()
    {
        foreach ($this->fields as $field) {
            if ($this->isPlaceHolderAsLabel()) {
                $field->getElement()->setAttribute('placeholder', $field->getElement()->getLabel());
            }
        }

        $this->addHiddenField(self::WEB_FORM_ID, $this->getFormId());
    }
}