<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm;

use Exception;
use InvalidArgumentException;
use KikCMS\Classes\Finder\Finder;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\Renderable\Filters;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\Translator;
use KikCMS\Classes\WebForm\Fields\DateField;
use KikCMS\Classes\WebForm\Fields\DataTableField;
use KikCMS\Classes\WebForm\Fields\FileInputField;
use KikCMS\Classes\WebForm\Fields\SelectDataTableField;
use KikCMS\Classes\WebForm\Fields\SelectField;
use KikCMS\Config\StatusCodes;
use KikCMS\ObjectLists\FieldMap;
use KikCMS\Services\Util\DateTimeService;
use Phalcon\Forms\ElementInterface;
use Phalcon\Forms\Form;
use Phalcon\Http\Response;
use Phalcon\Mvc\View;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Date;

/**
 * @property AccessControl $acl
 * @property View $view
 * @property Validation $validation
 * @property Translator $translator
 * @property DateTimeService $dateTimeService
 */
abstract class WebForm extends Renderable
{
    use FieldShortcuts;

    const WEB_FORM_ID    = 'webFormId';
    const WEB_FORM_CLASS = 'webFormClass';
    const FLASH_KEY      = 'flashForForm';

    /** @var FieldMap */
    protected $fieldMap;

    /** @var Tab[] */
    protected $tabs = [];

    /** @var array tracks field key increments */
    protected $keys = [];

    /** @var string */
    protected $formTemplate = 'form';

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

    /** @var string */
    protected $sendButtonLabel;

    /** @var string */
    protected $sendButtonClass = 'btn btn-submit btn-primary';

    /** @var Form */
    private $form;

    /** @var bool */
    private $placeHolderAsLabel = false;

    /** @var callable */
    private $successAction;

    /** @var callable */
    private $validateAction;

    /** @var array|null */
    private $input = null;

    /**
     * @inheritdoc
     */
    public function __construct(Filters $filters = null)
    {
        parent::__construct($filters);

        $this->fieldMap = new FieldMap();
    }

    /**
     * Adds required assets
     */
    public function addAssets()
    {
        if ($this->hasFieldWithType(Field::TYPE_FILE)) {
            (new Finder())->addAssets();
        }
    }

    /**
     * @param Field $field
     * @param Tab|null $tab
     * @return Field
     */
    public function addField(Field $field, Tab $tab = null): Field
    {
        $field->setForm($this);

        if ($this->fieldMap->has($field->getKey())) {
            throw new Exception('A field with key "' . $field->getKey() . '" already exists');
        }

        $this->fieldMap->add($field, $field->getKey());

        if ($field->getElement()) {
            $this->getForm()->add($field->getElement());
        }

        if ($tab) {
            $tab->addField($field);
        }

        return $field;
    }

    /**
     * @param string $name
     * @param Field[] $fields
     *
     * @return Tab
     */
    public function addTab(string $name, array $fields): Tab
    {
        $tab = new Tab($name, $fields);

        foreach ($fields as $key => $field) {
            $field->setTab($tab);
        }

        $this->tabs[] = $tab;

        return $tab;
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
        if ( ! $this->getForm()->has($fieldKey)) {
            return null;
        }

        return $this->getForm()->get($fieldKey);
    }

    /**
     * @return FieldMap
     */
    public function getFieldMap(): FieldMap
    {
        return $this->fieldMap;
    }

    /**
     * Get the form's input. Json encoded object will be converted to an PHP object.
     *
     * @return array
     */
    public function getInput(): array
    {
        if ($this->input !== null) {
            return $this->input;
        }

        $this->input = $this->request->getPost();

        foreach ($this->input as $key => $value) {
            if ( ! $this->fieldMap->has($key)) {
                continue;
            }

            $this->input[$key] = $this->fieldMap->get($key)->getInput($value);
        }

        return $this->input;
    }

    /**
     * Get the form's input but checkbox values will be booleans instead of non-present or 'on'
     *
     * @return array
     */
    public function getInputBooleanCheckbox(): array
    {
        $input = $this->getInput();

        foreach ($this->getFieldMap() as $key => $field){
            // in case of a checkbox, set the value by its existence
            if ($field->getType() == Field::TYPE_CHECKBOX) {
                $input[$key] = isset($input[$key]) ? 1 : 0;
            }

            // in case of a multicheckbox, and nothing is present, set value as an empty array
            if ( ! array_key_exists($key, $input) && Field::TYPE_MULTI_CHECKBOX) {
                $input[$key] = [];
            }
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
        $this->addAssets();

        if ($this->isPosted()) {
            $this->reUseDataTableInstances();
            $errorContainer = $this->getErrors();
            $this->updateFieldsByPostData();

            if ($errorContainer->isEmpty()) {
                $result = $this->successAction($this->getInputBooleanCheckbox());

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
     * @param string $sendButtonLabel
     *
     * @return WebForm|$this
     */
    public function setSendButtonLabel(string $sendButtonLabel)
    {
        $this->sendButtonLabel = $sendButtonLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormId(): string
    {
        return self::WEB_FORM_ID . '_' . str_replace('\\', '', static::class);
    }

    /**
     * @return string
     */
    public function getSendButtonLabel(): string
    {
        if ( ! $this->sendButtonLabel) {
            $this->sendButtonLabel = $this->translator->tl('webform.defaultSendLabel');
        }

        return $this->sendButtonLabel;
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
        return $this->request->getPost($this->getFormId()) == $this->getFormId();
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
     * @return WebForm|$this
     */
    public function initializeForm()
    {
        $this->initialize();

        $this->addHiddenField($this->getFormId(), $this->getFormId());

        if ($this->isPlaceHolderAsLabel()) {
            foreach ($this->fieldMap as $field) {
                $field->setAttribute('placeholder', $field->getElement()->getLabel());
            }
        }

        // add select field placeholders
        foreach ($this->fieldMap as $key => $field) {
            if ($field instanceOf SelectField && $field->getAddPlaceholder()) {
                $placeHolderLabel = $this->translator->tl('webform.selectPlaceHolderLabel');
                $field->getElement()->setOptions(['' => $placeHolderLabel] + $field->getElement()->getOptions());
            }

            if ($field instanceof DateField) {
                $this->initializeDateField($field);
            }
        }

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
     * Make sure only this for will output flash messages
     */
    public function flashForFormOnly()
    {
        $sessionKey = self::FLASH_KEY;

        $this->session->$sessionKey = $this->getFormId();
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
     * @return null|Response|string
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
     * Pre-renders the DataTable fields, so that any required asset will be correctly added
     */
    protected function renderDataTableFields()
    {
        /** @var SelectDataTableField|DataTableField $field */
        foreach ($this->getFieldMap() as $key => $field) {
            if ($field instanceof SelectDataTableField) {
                $this->renderSelectDataTableField($field);
            }

            if ($field instanceof DataTableField) {
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
            'class'                  => static::class,
            'requestUri'             => $this->request->getServer('REQUEST_URI'),
            'allowedFinderAccess'    => $this->acl->allowed(Permission::ACCESS_FINDER),
            'security'               => $this->security,
            'form'                   => $this->getForm(),
            'fields'                 => $this->fieldMap,
            'tabs'                   => $this->tabs,
            'filters'                => $this->filters,
            'displaySendButton'      => $this->displaySendButton,
            'sendButtonClass'        => $this->sendButtonClass,
            'currentTab'             => $this->getCurrentTab(),
            'fieldsWithoutContainer' => $this->getFieldsWithoutContainer(),
            'formId'                 => $this->getFormId(),
            'sendButtonLabel'        => $this->getSendButtonLabel(),
            'placeHolderAsLabel'     => $this->isPlaceHolderAsLabel(),
            'instance'               => $this->getInstance(),
            'jsData'                 => $this->getJsData(),
            'mayFlash'               => $this->mayFlash(),
            'encType'                => $this->getEncType(),
            'errorContainer'         => $errorContainer,
            'webForm'                => $this,
        ]);
    }

    /**
     * @param DataTableField $field
     */
    protected function renderDataTableField(DataTableField $field)
    {
        $field->setRenderedDataTable($field->getDataTable()->render());
    }

    /**
     * @param SelectDataTableField $field
     */
    protected function renderSelectDataTableField(SelectDataTableField $field)
    {
        // set selected ids filter for SelectDataTable
        if ($value = $field->getElement()->getValue()) {
            $field->getDataTable()->getFilters()->setSelectedValues((array) json_decode($value));
        }

        $field->setRenderedDataTable($field->getDataTable()->render());
    }

    /**
     * @return string
     */
    private function getEncType(): string
    {
        foreach ($this->getFieldMap() as $field){
            if($field instanceof FileInputField){
                return 'multipart/form-data';
            }
        }

        return 'application/x-www-form-urlencoded';
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

        if ($this->getForm()->isValid($this->getInput() + $_FILES) && $errorContainer->isEmpty()) {
            return $errorContainer;
        }

        foreach ($this->getForm()->getElements() as $formElement) {
            $elementName     = $formElement->getName();
            $elementMessages = $this->getForm()->getMessagesFor($elementName);

            if ( ! $elementMessages) {
                continue;
            }

            foreach ($elementMessages as $message) {
                $message = $message->getMessage();

                if($formElement->getLabel()){
                    $message = str_replace(':label', "'" . strip_tags((string) $formElement->getLabel()) . "'", $message);
                }

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
     * Get all fields that are not in a Tab or Section
     *
     * return Field[]
     */
    private function getFieldsWithoutContainer()
    {
        $fieldsWithoutContainer = [];

        foreach ($this->fieldMap as $field) {
            if ( ! $field->getTab() && ! $field->getSection()) {
                $fieldsWithoutContainer[] = $field;
            }
        }

        return $fieldsWithoutContainer;
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
     * If no validator is provided, add one based on the given format
     *
     * @param DateField $field
     */
    private function initializeDateField(DateField $field)
    {
        if ($field->getElement()->getValidators()) {
            return;
        }

        if ( ! $field->getFormat()) {
            $field->setFormat($this->dateTimeService->getDateFormat());
        }

        $validator = new Date([
            "format"     => $field->getFormat(),
            "allowEmpty" => true,
        ]);

        $field->getElement()->addValidator($validator);
    }

    /**
     * Update the forms' input after a post is done
     */
    private function updateFieldsByPostData()
    {
        foreach ($this->fieldMap as $key => $field) {
            // set unchecked checkboxes (with default = 1) to default 0
            if ($field->getType() == Field::TYPE_CHECKBOX && ! $this->request->hasPost($key)) {
                $field->setDefault(0);
            }

            // set unposted multi-checkboxes to default empty
            if ($field->getType() == Field::TYPE_MULTI_CHECKBOX && ! $this->request->hasPost($key)) {
                $field->setDefault([]);
            }
        }
    }

    /**
     * @return bool
     */
    private function mayFlash(): bool
    {
        $sessionKey = self::FLASH_KEY;

        if ( ! $this->session->$sessionKey) {
            return true;
        }

        if ($this->session->$sessionKey != $this->getFormId()) {
            return false;
        }

        $this->session->remove($sessionKey);

        return true;
    }

    /**
     * Make sure datatable instance keys are re-used after a post
     */
    private function reUseDataTableInstances()
    {
        foreach ($this->fieldMap as $key => $field) {
            if ($field instanceOf DataTableField && $this->request->hasPost($key)) {
                $instance = $this->request->getPost($key);
                $field->getDataTable()->setInstance($instance);
                $field->setDefault($instance);
            }
        }
    }

    /**
     * @return Form
     */
    private function getForm(): Form
    {
        if ( ! $this->form) {
            $this->form = new Form();
            $this->form->setValidation($this->validation);
        }

        return $this->form;
    }
}