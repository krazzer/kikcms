<?php declare(strict_types=1);


namespace KikCMS\Classes\WebForm\Fields;


use KikCMS\Classes\WebForm\Field;
use Phalcon\Forms\Element\Hidden;

class ReCaptchaField extends Field
{
    /** @var int */
    private $version;

    /**
     * @param string $label
     * @param int $version
     * @param array $validators
     */
    public function __construct(string $label = null, int $version = 2, array $validators = [])
    {
        $this->key     = 'captcha';
        $this->label   = $label;
        $this->version = $version;

        $this->element = (new Hidden($this->key))->addValidators($validators);
        $this->element->setAttribute('class', 'webform-field-recaptcha');
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Field::TYPE_RECAPTCHA;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     * @return ReCaptchaField
     */
    public function setVersion(int $version): ReCaptchaField
    {
        $this->version = $version;
        return $this;
    }
}