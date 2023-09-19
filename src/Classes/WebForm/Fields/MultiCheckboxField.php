<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Check;

class MultiCheckboxField extends Field
{
    /** @var array */
    private $options;

    /** @var bool allow a different value to be entered */
    private $different = false;

    /**
     * @param string $key
     * @param string $label
     * @param array $options
     * @param array $validators
     */
    public function __construct(string $key, string $label, array $options, array $validators = [])
    {
        $element = (new Check($key))
            ->setAttribute('type', 'multiCheckbox')
            ->addValidators($validators)
            ->setLabel($label);

        $this->element = $element;
        $this->options = $options;
        $this->key     = $key;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isset($key): bool
    {
        return in_array($key, (array) $this->element->getValue());
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return Field::TYPE_MULTI_CHECKBOX;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return MultiCheckboxField
     */
    public function setOptions(array $options): MultiCheckboxField
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormFormat($value): mixed
    {
        return is_string($value) ? json_decode($value) : $value;
    }

    /**
     * @return bool
     */
    public function allowsDifferent(): bool
    {
        return $this->different;
    }

    /**
     * @param bool $different
     * @return MultiCheckboxField
     */
    public function allowDifferent(bool $different = true): MultiCheckboxField
    {
        $this->different = $different;
        return $this;
    }
}