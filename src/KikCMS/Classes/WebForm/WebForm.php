<?php

namespace KikCMS\Classes\WebForm;

use InvalidArgumentException;
use KikCMS\Classes\Translator;
use Phalcon\Di\Injectable;
use Phalcon\Forms\Element;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\ElementInterface;
use Phalcon\Forms\Form;
use Phalcon\Http\Response;
use Phalcon\Mvc\View;
use Phalcon\Validation;

/** @property View $view */
/** @property Validation $validation */
/** @property Translator $translator */
class WebForm extends Injectable
{
    /** @var Element[] */
    private $fields;

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
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @param Element $field
     */
    public function addField(Element $field)
    {
        $this->fields[$field->getName()] = $field;
        $this->form->add($field);
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function addPasswordField(string $key, string $label, array $validators)
    {
        $password = new Password($key);
        $password->setLabel($label);
        $password->addValidators($validators);

        $this->addField($password);
    }

    /**
     * @param string $key
     * @param string $label
     * @param array $validators
     */
    public function addTextField(string $key, string $label, array $validators)
    {
        $name = new Text($key);
        $name->setLabel($label);
        $name->addValidators($validators);

        $this->addField($name);
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
    public function getInput(): array
    {
        $input = $_POST;

        unset($input['formId']);

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

        if ($this->formIsSend()) {
            $errorContainer = $this->getErrors();

            if ($errorContainer->isEmpty()) {
                $result = $this->successAction($this->getInput());

                if(is_string($result)){
                    return $result;
                }
            }
        }

        return $this->renderView('form', [
            'form'               => $this->form,
            'formId'             => $this->getFormId(),
            'sendButtonLabel'    => $this->getSendLabel(),
            'placeHolderAsLabel' => $this->isPlaceHolderAsLabel(),
            'errorContainer'     => $errorContainer,
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
     * @return bool
     */
    private function formIsSend()
    {
        return isset($_POST['formId']) && $_POST['formId'] == $this->getFormId();
    }

    /**
     * @return string
     */
    private function getFormId(): string
    {
        return static::class;
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
     * Verify the input with additional rules, if the input was valid, i.e. checking login credentials
     * Override this method if you have such rules
     *
     * @param array $input
     * @return ErrorContainer
     */
    protected function verify(array $input): ErrorContainer
    {
        if($this->verifyAction){
            return call_user_func($this->verifyAction, $input);
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
        if($this->successAction){
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
        if ($this->form->isValid($this->getInput())) {
            return $this->verify($this->getInput());
        }

        $errorContainer = new ErrorContainer();

        foreach ($this->form->getElements() as $formElement) {
            $elementName     = $formElement->getName();
            $elementMessages = $this->form->getMessagesFor($elementName);

            if (!$elementMessages) {
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
            $field->setAttribute('class', 'form-control');

            if ($this->isPlaceHolderAsLabel()) {
                $field->setAttribute('placeholder', $field->getLabel());
            }
        }
    }
}