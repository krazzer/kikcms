<?php


namespace KikCMS\Classes\Phalcon;


use KikCMS\Classes\Translator;
use KikCMS\Config\KikCMSConfig;
use Phalcon\Messages\Messages;
use Phalcon\Validation\ValidatorCompositeInterface;
use Phalcon\Validation\ValidatorInterface;

/**
 * @property Translator $translator
 */
class Validation extends \Phalcon\Validation
{
    /**
     * @inheritDoc
     */
    public function validate($data = null, $entity = null): Messages
    {
        $validatorFields = $this->getValidators();

        foreach ($validatorFields as $validators) {
            foreach ($validators as $validator) {
                if ($validator instanceof ValidatorCompositeInterface) {
                    foreach ($validator->getValidators() as $subValidator) {
                        $this->setDefaultTemplate($subValidator);
                    }
                } else {
                    $this->setDefaultTemplate($validator);
                }
            }
        }

        return parent::validate($data, $entity);
    }

    /**
     * @param ValidatorInterface $validator
     */
    private function setDefaultTemplate(ValidatorInterface $validator)
    {
        $className = get_class($validator);
        $className = str_replace([KikCMSConfig::NAMESPACE_PATH_PHALCON_VALIDATORS, '\\'], '', $className);

        $translation = $this->translator->tl('webform.messages.' . $className);

        $validator->setTemplate($translation);
    }
}