<?php

namespace KikCMS\Classes\WebForm;

use KikCMS\Classes\Translator;
use Phalcon\Di\Injectable;
use Phalcon\Forms\Element;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;
use Phalcon\Mvc\View;

/** @property View $view */
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

    /**
     * @param Element $field
     */
    public function addField(Element $field)
    {
        $this->fields[$field->getName()] = $field;
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
     * Render the form
     */
    public function render()
    {
        $this->form = new Form();

        $this->initialize();
        $this->initializeFields();

        $errors = [];

        if(count($_POST) > 0)
        {
            if (!$this->form->isValid($_POST)) {

                foreach($this->form->getElements() as $formElement)
                {
                    $elementName = $formElement->getName();
                    $elementMessages = $this->form->getMessagesFor($elementName);

                    if( ! $elementMessages){
                        continue;
                    }

                    $messages = [];

                    foreach ($elementMessages as $message)
                    {
                        $messages[] = $this->translator->tl('webform.' . $message->getType(), ['fieldName' => $formElement->getLabel()]);
                    }

                    $errors[$elementName] = implode("\n", $messages);

                    $class = $formElement->getAttribute('class');
                    $class = $class ? $class . ' has-errors' : 'has-errors';

                    $formElement->setAttribute('class', $class);
                }

//                $messages = $this->form->getMessages();
//
//                foreach ($messages as $message) {
//                    $messageString = $this->translator->tl('webform.' . $message->getType(), ['fieldName' => $message->getField()]);
//
//                    echo $messageString, "<br>";
//                }
            }
        }

        return $this->renderView('form', [
            'form'               => $this->form,
            'errors'             => $errors,
            'sendButtonLabel'    => $this->getSendLabel(),
            'placeHolderAsLabel' => $this->isPlaceHolderAsLabel(),
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
    public function renderView($viewName, array $parameters)
    {
        $this->view->setVars($parameters);

        return $this->view->partial('webform/' . $viewName);
    }

    /**
     * Override to build up the form
     */
    public function initialize(){}

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

            $this->form->add($field);
        }
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
}